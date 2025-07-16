<?php

namespace App\Services;

use App\Models\InventoryMovement;

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
}