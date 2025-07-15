<?php

namespace App\Services;

use App\Models\Purchase;

class ProcurementService
{
    // Calculate the total cost of the product
    public static function calcaulteTotalCost($supplier, $product, $quantity)
    {
        // Calculate the total cost of the product
        $supplierProduct = $supplier->products()->where('product_id', $product->id)->first();
        $salePrice = $supplierProduct->real_sale_price;

        $shippingCost = $supplier->real_shipping_cost;
        $customsDuties = 0;
        
        if($supplier->country && $supplier->country->customs_duties > 0) {
            $customsDuties = $supplier->country->customs_duties;
        }
        
        return $salePrice * $quantity + $shippingCost * $quantity + $customsDuties * ($quantity + $shippingCost);
    }

    public static function validatePurchase($company, $supplier, $product, $quantity){
        $errors = [];

        $totalCost = self::calcaulteTotalCost($supplier, $product, $quantity);

        // Check if company has sufficient funds
        if (!FinanceService::haveSufficientFunds($company, $totalCost)) {
            $errors['funds'] = 'You do not have enough funds to purchase this product. Required: DZD ' . $totalCost . ', Available: DZD ' . $company->funds;
        }

        if($supplier->country && !$supplier->country->allow_imports) {
            $errors['country_id'] = 'This supplier is in a country that our government does not allow imports from.';
        }

        $supplierProduct = $supplier->products()->where('product_id', $product->id)->first();

        if($quantity < $supplierProduct->minimum_order_qty) {
            $errors['quantity'] = 'You must order at least ' . $supplierProduct->minimum_order_qty . ' units of this product.';
        }
        
        return $errors;
    }

    public static function purchase($company, $supplier, $product, $quantity){
        $supplierProduct = $supplier->products()->where('product_id', $product->id)->first();

        $salePrice = $supplierProduct->real_sale_price;

        $shippingCost = $supplier->real_shipping_cost;
        $customsDuties = 0;
        
        if($supplier->country && $supplier->country->customs_duties > 0) {
            $customsDuties = $supplier->country->customs_duties;
        }

        $totalCost = self::calcaulteTotalCost($supplier, $product, $quantity);

        // Get current timestamp
        $orderedAt = SettingsService::getCurrentTimestamp();
        $estimatedArrivalAt = $orderedAt->copy()->addDays($supplier->avg_shipping_time_days);

        // Calculate real delivery date
        $randomDays = CalculationsService::calculatePertValue($supplier->min_shipping_time_days, $supplier->avg_shipping_time_days, $supplier->max_shipping_time_days);
        $realDeliveredAt = $orderedAt->copy()->addDays($randomDays);

        // Create purchase
        $purchase = Purchase::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'sale_price' => $salePrice,
            'shipping_cost' => $shippingCost,
            'customs_duties' => $customsDuties,
            'total_cost' => $totalCost,
            'carbon_footprint' => $supplier->carbon_footprint,
            'shipping_time_days' => $supplier->avg_shipping_time_days,
            'status' => Purchase::STATUS_ORDERED,
            'ordered_at' => $orderedAt,
            'estimated_delivered_at' => $estimatedArrivalAt,
            'real_delivered_at' => $realDeliveredAt,
        ]);

        FinanceService::payPurchase($company, $purchase);
        PollutionService::releaseCarbonFootprint($company, $supplier->carbon_footprint * $quantity);

        NotificationService::createPurchaseOrderedNotification($purchase);

        return $purchase;
    }   

    public static function deliveredPurchase($purchase){
        $purchase->update([
            'status' => Purchase::STATUS_DELIVERED,
            'delivered_at' => SettingsService::getCurrentTimestamp(),
        ]);

        NotificationService::createPurchaseDeliveredNotification($purchase);
    }
}