<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\SupplierProduct;

class ProcurementService
{
    //Change supplier prices and costs
    public static function changeSupplierPricesAndCosts($supplier){
        $supplier->update([
            'real_shipping_time_days' => CalculationsService::calcaulteRandomBetweenMinMax($supplier->min_shipping_time_days, $supplier->max_shipping_time_days),
            'real_shipping_cost' => CalculationsService::calcaulteRandomBetweenMinMax($supplier->min_shipping_cost, $supplier->max_shipping_cost),
        ]);

        $supplierProducts = $supplier->supplierProducts()->get();

        foreach($supplierProducts as $supplierProduct){
            $supplierProduct->update([
                'real_sale_price' => CalculationsService::calcaulteRandomBetweenMinMax($supplierProduct->min_sale_price, $supplierProduct->max_sale_price),
            ]);
        }
    }

    // Calculate the total cost of the product
    public static function calcaulteTotalCost($supplier, $product, $quantity)
    {
        // Calculate the total cost of the product
        $supplierProduct = SupplierProduct::where([
            'supplier_id' => $supplier->id, 
            'product_id' => $product->id
        ])->first();

        // Get the real sale price of the product
        $salePrice = $supplierProduct->real_sale_price;

        // Get the real shipping cost of the supplier
        $shippingCost = $supplier->real_shipping_cost;

        // Get the customs duties rate of the supplier
        $customsDuties = 0;

        if($supplier->country) {
            $customsDuties = $supplier->country->customs_duties_rate;
        }

        // Calculate the total cost of the product
        $totalSalePrice = $salePrice * $quantity;
        $totalShippingCost = $shippingCost * $quantity;
        $totalCustomsDuties = $customsDuties * ($totalSalePrice + $totalShippingCost);

        return $totalSalePrice + $totalShippingCost + $totalCustomsDuties;
    }

    // Initiate a purchase
    public static function purchase($company, $supplier, $product, $quantity){
        return;
        
        $supplierProduct = SupplierProduct::where([
            'supplier_id' => $supplier->id, 
            'product_id' => $product->id
        ])->first();

        $salePrice = $supplierProduct->real_sale_price;

        $shippingCost = $supplier->real_shipping_cost;
        $customsDuties = 0;
        
        if($supplier->country && $supplier->country->customs_duties_rate > 0) {
            $customsDuties = $supplier->country->customs_duties_rate;
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

        InventoryService::purchaseDelivered($purchase);
        NotificationService::createPurchaseDeliveredNotification($purchase);
    }
}