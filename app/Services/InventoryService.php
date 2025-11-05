<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    //-------------------------------------
    // Inventory
    //-------------------------------------
    public static function haveSufficientStock($company, $product, $quantity){
        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        
        if(!$companyProduct){
            return false;
        }
        
        return $companyProduct->available_stock >= $quantity;
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function purchaseDelivered($purchase){
        $company = $purchase->company;
        $product = $purchase->product;
        $quantity = $purchase->quantity;

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        $companyProduct->increment('available_stock', $quantity);

        $inventoryMovement = InventoryMovement::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
            'original_quantity' => $quantity,
            'current_quantity' => $quantity,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'moved_at' => SettingsService::getCurrentTimestamp(),
        ]);
    }

    //-------------------------------------
    // Expiration
    //-------------------------------------
    public static function expireInventory($company){
        $products = Product::where('has_expiration', true)->get();

        foreach($products as $product){
            $expiredQuantity = 0;

            $companyInventory = $company->inventoryMovements()->where([
                'product_id' => $product->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
            ])->get();

            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();

            if(!$companyInventory || !$companyProduct){
                continue;
            }

            $currentTimestamp = SettingsService::getCurrentTimestamp();

            foreach($companyInventory as $inventory){
                $expiresAt = $inventory->moved_at->addDays($product->shelf_life_days);

                if($expiresAt->isAfter($currentTimestamp)){
                    continue;
                }

                $leftAvailableStock = $inventory->current_quantity;

                if($leftAvailableStock <= 0){
                    continue;
                }

                InventoryMovement::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'movement_type' => InventoryMovement::MOVEMENT_TYPE_EXPIRED,
                    'original_quantity' => $leftAvailableStock,
                    'current_quantity' => $leftAvailableStock,
                    'moved_at' => $currentTimestamp,
                ]);
    
                $companyProduct->decrement('available_stock', $leftAvailableStock);
                $inventory->update(['current_quantity' => 0]);

                $expiredQuantity += $leftAvailableStock;
            }

            if($expiredQuantity > 0){
                NotificationService::createInventoryExpiredNotification($company, $product, $expiredQuantity);
            }
        }
    }

    //-------------------------------------
    // Inventory costs
    //-------------------------------------
    public static function payInventoryCosts($company){
        $products = Product::where('storage_cost', '>', 0)->get();

        foreach($products as $product){
            $leftAvailableStock = $company->inventoryMovements()->where([
                'product_id' => $product->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
            ])->sum('current_quantity');

            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();

            if(!$companyProduct || $leftAvailableStock <= 0){
                continue;
            }

            $totalCost = $product->storage_cost * $leftAvailableStock;
            
            if($totalCost > 0){
                FinanceService::payInventoryCosts($company, $product, $totalCost);
                NotificationService::createInventoryCostsPaidNotification($company, $product, $leftAvailableStock, $totalCost);
            }
        }
    }

    //-------------------------------------
    // Sales
    //-------------------------------------
    public static function saleDelivered($sale){
        DB::transaction(function () use ($sale) {
            $company = $sale->company;
            $product = $sale->product;
            $quantity = $sale->quantity;

            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
            $companyProduct->decrement('available_stock', $quantity);

            // Get all IN movements for this product, ordered by moved_at (FIFO)
            // Use lockForUpdate to prevent race conditions on FIFO inventory
            $companyInventories = $company->inventoryMovements()->where([
                'product_id' => $product->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN
            ])->where('current_quantity', '>', 0)->orderBy('moved_at', 'asc')->lockForUpdate()->get();

            $remainingQuantity = $quantity;

            // Apply FIFO: subtract from oldest inventory first
            foreach($companyInventories as $companyInventory){
                if($remainingQuantity <= 0){
                    break; // We've allocated all the sale quantity
                }

                $availableInThisBatch = $companyInventory->current_quantity;
                $quantityToSubtract = min($remainingQuantity, $availableInThisBatch);

                // Update the inventory movement
                $companyInventory->decrement('current_quantity', $quantityToSubtract);

                $remainingQuantity -= $quantityToSubtract;
            }

            // Create the OUT movement for the sale
            $inventoryMovement = InventoryMovement::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_OUT,
                'original_quantity' => $quantity,
                'current_quantity' => $quantity,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'moved_at' => SettingsService::getCurrentTimestamp(),
            ]);
        });
    }

    //-------------------------------------
    // Production
    //-------------------------------------
    public static function productionStarted($productionOrder, $material, $requiredQuantity){
        DB::transaction(function () use ($productionOrder, $material, $requiredQuantity) {
            $company = $productionOrder->companyMachine->company;
            $quantity = $requiredQuantity;

            // Use lockForUpdate to prevent race conditions on FIFO inventory
            // Filter to only batches with available stock
            $companyInventory = $company->inventoryMovements()->where([
                'product_id' => $material->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
            ])->where('current_quantity', '>', 0)
              ->orderBy('moved_at', 'asc') // FIFO order
              ->lockForUpdate()
              ->get();

            $remainingQuantity = $quantity;
            $actualQuantityConsumed = 0;

            foreach($companyInventory as $inventory){
                if($remainingQuantity <= 0){
                    break; // We've consumed all needed
                }

                $availableInThisBatch = max(0, $inventory->current_quantity); // Ensure non-negative
                $quantityToSubtract = min($remainingQuantity, $availableInThisBatch);

                // Only decrement if there's something to subtract
                if($quantityToSubtract > 0){
                    // Ensure we don't go below 0
                    $newQuantity = max(0, $inventory->current_quantity - $quantityToSubtract);
                    $inventory->update(['current_quantity' => $newQuantity]);
                    
                    $actualQuantityConsumed += $quantityToSubtract;
                    $remainingQuantity -= $quantityToSubtract;
                }
            }

            $companyProduct = $company->companyProducts()->where('product_id', $material->id)->first();
            
            // Use actual quantity consumed, not the full quantity, and ensure it doesn't go below 0
            $stockToDecrement = min($actualQuantityConsumed, $companyProduct->available_stock);
            $newAvailableStock = max(0, $companyProduct->available_stock - $stockToDecrement);
            $companyProduct->update(['available_stock' => $newAvailableStock]);

            $inventoryMovement = InventoryMovement::create([
                'company_id' => $company->id,
                'product_id' => $material->id,
                'movement_type' => InventoryMovement::MOVEMENT_TYPE_OUT,
                'original_quantity' => $quantity,
                'current_quantity' => $actualQuantityConsumed, // Use actual consumed amount
                'reference_type' => 'production',
                'reference_id' => $productionOrder->id,
                'moved_at' => SettingsService::getCurrentTimestamp(),
            ]);
        });
    }

    //-------------------------------------
    // Production
    //-------------------------------------
    public static function productionCompleted($productionOrder, $outputQuantity){
        $company = $productionOrder->companyMachine->company;
        $product = $productionOrder->product;

        $companyInventory = InventoryMovement::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
            'original_quantity' => $outputQuantity,
            'current_quantity' => $outputQuantity,
            'reference_type' => 'production',
            'reference_id' => $productionOrder->id,
            'moved_at' => SettingsService::getCurrentTimestamp(),
        ]);

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        $companyProduct->increment('available_stock', $outputQuantity);
    }
}