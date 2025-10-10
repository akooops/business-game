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
        // Get the supplier product
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
        if($supplier->country && $supplier->country->customs_duties_rate > 0) {
            $customsDuties = $supplier->country->customs_duties_rate;
        }

        // Calculate the total cost of the product
        $totalCost = self::calcaulteTotalCost($supplier, $product, $quantity);

        // Get current timestamp
        $orderedAt = SettingsService::getCurrentTimestamp();

        // Create purchase
        $purchase = Purchase::create([
            'quantity' => $quantity,
            'sale_price' => $salePrice,
            'shipping_cost' => $shippingCost,
            'customs_duties' => $customsDuties,
            'total_cost' => $totalCost,
            'carbon_footprint' => $supplier->carbon_footprint,
            'shipping_time_days' => $supplier->real_shipping_time_days,
            'status' => Purchase::STATUS_ORDERED,
            'ordered_at' => $orderedAt,
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
        ]);

        // Release carbon footprint
        $carbonFootprint = $supplier->carbon_footprint;
        PollutionService::releaseCarbonFootprint($company, $carbonFootprint);

        // Pay for the purchase
        FinanceService::payPurchase($company, $purchase);
        NotificationService::createPurchaseOrderedNotification($company, $purchase, $supplier, $product, $quantity);
    }   

    public static function processDeliveredPurchase($company){
        $companyPurchases = $company->purchases()->where('status', Purchase::STATUS_ORDERED)->get();

        foreach($companyPurchases as $companyPurchase){
            // Check if purchase is delivered
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $deliveredAt = $companyPurchase->ordered_at->copy()->addDays($companyPurchase->shipping_time_days);

            if($deliveredAt <= $currentTimestamp){
                // Update purchase status
                $companyPurchase->update([
                    'status' => Purchase::STATUS_DELIVERED,
                    'delivered_at' => $currentTimestamp,
                ]);

                // Add to inventory
                InventoryService::purchaseDelivered($companyPurchase);

                // Create notification
                NotificationService::createPurchaseDeliveredNotification($company, $companyPurchase, $companyPurchase->supplier, $companyPurchase->product, $companyPurchase->quantity);
            }
        }
    }
}