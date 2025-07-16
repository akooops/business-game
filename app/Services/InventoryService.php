<?php

namespace App\Services;

class InventoryService
{
    public static function addStock($company, $product, $quantity){
        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        $companyProduct->update(['available_stock' => $companyProduct->available_stock + $quantity]);
    }
}