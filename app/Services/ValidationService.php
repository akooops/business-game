<?php

namespace App\Services;

use App\Models\SupplierProduct;

class ValidationService
{
    //-------------------------------------
    // Technologies
    //-------------------------------------
    public static function validateTechnologyResearch($company, $technology){
        $errors = [];

        // Check if company has sufficient funds
        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $technology->research_cost);
        if(!$hasSufficientFunds){
            $errors['funds'] = 'You do not have enough funds to research this technology.';
        }

        // Check if company is already researching this technology
        $alreadyResearching = $company->companyTechnologies()
            ->where('technology_id', $technology->id)
            ->exists();
        
        if ($alreadyResearching) {
            $errors['technology_id'] = 'You are already researching this technology.';
            return $errors;
        }

        // Check research level
        if ($technology->level > $company->research_level + 1) {
            $errors['technology_id'] = 'You can only research technologies up to level ' . ($company->research_level + 1) . '.';
        }

        return $errors;
    }

    //-------------------------------------
    // Products
    //-------------------------------------
    public static function validateProductSalePriceChange($company, $product){
        $errors = [];

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();

        if(!$companyProduct){
            $errors['product_id'] = 'This product is not in researched yet by your company';
        }

        return $errors;
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function validatePurchase($company, $supplier, $product, $quantity){
        $errors = [];

        if(!$supplier){
            $errors['supplier_id'] = 'The selected supplier does not exist.';
        }

        if(!$product){
            $errors['product_id'] = 'The selected product does not exist.';
        }

        if(!$product->is_researched){
            $errors['product_id'] = 'This product is not researched yet.';
        }

        $totalCost = ProcurementService::calcaulteTotalCost($supplier, $product, $quantity);
        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $totalCost);

        if(!$hasSufficientFunds){
            $errors['funds'] = 'You do not have enough funds to purchase this product.';
        }

        if($supplier->country && !$supplier->country->allows_imports) {
            $errors['country_id'] = 'This supplier is in a country that our government blocked imports from.';
        }

        $supplierProduct = SupplierProduct::where([
            'supplier_id' => $supplier->id, 
            'product_id' => $product->id
        ])->first();

        if(!$supplierProduct) {
            $errors['supplier_product'] = 'This supplier does not sell this product.';
        }
        
        return $errors;
    }
}