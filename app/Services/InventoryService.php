<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;

class InventoryService
{
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
            'quantity' => $quantity,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'moved_at' => SettingsService::getCurrentTimestamp(),
        ]);
    }

    public static function expireInventory($company){
        $products = Product::where('has_expiration', true)->get();

        foreach($products as $product){
            $expiredQuantity = 0;
            $companyInventory = $company->inventoryMovements()->where('product_id', $product->id)->get();
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

                InventoryMovement::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'movement_type' => InventoryMovement::MOVEMENT_TYPE_EXPIRED,
                    'quantity' => $inventory->quantity,
                    'moved_at' => $currentTimestamp,
                ]);
    
                $companyProduct->update(['available_stock' => $companyProduct->available_stock - $inventory->quantity]);

                $expiredQuantity += $inventory->quantity;
            }

            if($expiredQuantity > 0){
                NotificationService::createInventoryExpiredNotification($company, $product, $expiredQuantity);
            }
        }
    }
}