<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;

class InventoryService
{
    public static function haveSufficientStock($company, $product, $quantity){
        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        
        if(!$companyProduct){
            return false;
        }
        
        return $companyProduct->available_stock >= $quantity;
    }

    public static function purchaseDelivered($purchase){
        $company = $purchase->company;
        $product = $purchase->product;
        $quantity = $purchase->quantity;

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        $companyProduct->update(['available_stock' => $companyProduct->available_stock + $quantity]);

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

                $leftAvailableStock = $inventory->original_quantity - $inventory->current_quantity;

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
    
                $companyProduct->update(['available_stock' => $companyProduct->available_stock - $leftAvailableStock]);

                $expiredQuantity += $leftAvailableStock;
            }

            if($expiredQuantity > 0){
                NotificationService::createInventoryExpiredNotification($company, $product, $expiredQuantity);
            }
        }
    }

    public static function saleConfirmed($sale){
        $company = $sale->company;
        $product = $sale->product;
        $quantity = $sale->quantity;

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        $companyProduct->update(['available_stock' => $companyProduct->available_stock - $quantity]);

        // Get all IN movements for this product, ordered by moved_at (FIFO)
        $companyInventories = $company->inventoryMovements()->where([
            'product_id' => $product->id,
            'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN
        ])->where('current_quantity', '>', 0)->orderBy('moved_at', 'asc')->get();

        $remainingQuantity = $quantity;

        // Apply FIFO: subtract from oldest inventory first
        foreach($companyInventories as $companyInventory){
            if($remainingQuantity <= 0){
                break; // We've allocated all the sale quantity
            }

            $availableInThisBatch = $companyInventory->current_quantity;
            $quantityToSubtract = min($remainingQuantity, $availableInThisBatch);

            // Update the inventory movement
            $companyInventory->update([
                'current_quantity' => $availableInThisBatch - $quantityToSubtract
            ]);

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
    }
}