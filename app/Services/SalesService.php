<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Wilaya;

class SalesService
{
    // Get the current gameweek product market price
    public static function getCurrentGameweekProductMarketPrice($product)
    {
        $productDemand = $product->demands()->where('gameweek', SettingsService::getCurrentGameWeek())->first();

        return ($productDemand) ? $productDemand->market_price : $product->avg_market_price;
    }

    //Fix the sale price of a product
    public static function fixProductSalePrice($company, $product, $salePrice){
        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
        
        $companyProduct->update([
            'sale_price' => $salePrice,
        ]);
    }

    //Change wilaya shipping costs
    public static function changeWilayaShippingCosts($wilaya){
        $wilaya->update([
            'real_shipping_time_days' => CalculationsService::calcaulteRandomBetweenMinMax($wilaya->min_shipping_time_days, $wilaya->max_shipping_time_days),
            'real_shipping_cost' => CalculationsService::calcaulteRandomBetweenMinMax($wilaya->min_shipping_cost, $wilaya->max_shipping_cost),
        ]);
    }

    // Generate demand for a company
    public static function generateDemand($company)
    {
        // Get all company products
        $companyProducts = $company->companyProducts()->get();
        
        // Collect all products with new sales for batched notification
        $salesGenerated = [];

        foreach($companyProducts as $companyProduct){
            // Get the product demand for the current gameweek
            $product = $companyProduct->product;
            if(!$product->is_saleable) continue;
            
            $productDemand = $product->demands()->where('gameweek', SettingsService::getCurrentGameWeek())->first();

            $marketPrice = ($productDemand) ? $productDemand->market_price : $product->avg_market_price; 
            $realDemand = ($productDemand) ? $productDemand->real_demand : $product->avg_demand;

            // Calculate the price difference between the market price and the company product sale price
            $priceDifference = $marketPrice - $companyProduct->sale_price;

            // Get the ad market impact percentage
            $adMarketImpactPercentage = AdsService::getAdMarketImpactPercentage($company, $product);

            // Calculate the demand for the week based on the price difference and the elasticity coefficient with marketing boost
            $baseDemand = $realDemand * (1 + $adMarketImpactPercentage);
            
            // Prevent division by zero if market price is 0
            if ($marketPrice > 0) {
                $demandForWeek = $baseDemand - $product->elasticity_coefficient * $baseDemand * ($priceDifference / $marketPrice);
            } else {
                // If no market price exists, use base demand without price elasticity adjustment
                $demandForWeek = $baseDemand;
            }

            if($productDemand){
                if($demandForWeek > $productDemand->max_demand){
                    $demandForWeek = $productDemand->max_demand;
                }

                if($demandForWeek < 0){
                    $demandForWeek = 0;
                }
            }else{
                if($demandForWeek > $product->avg_demand * 1.2){
                    $demandForWeek = $product->avg_demand;
                }

                if($demandForWeek < 0){
                    $demandForWeek = 0;
                }
            }

            // Create one sale with the full demand
            $saleDemand = round($demandForWeek);

            // Skip if there's no demand
            if ($saleDemand <= 0) {
                continue;
            }

            // Get a random wilaya
            $randomWilaya = Wilaya::inRandomOrder()->first();

            // Get the wilaya shipping cost
            $wilayaShippingCost = $randomWilaya->real_shipping_cost;

            // Get the current gameweek and the timelimit days
            $gameWeek = SettingsService::getCurrentGameWeek();
            $timelimit_days = CalculationsService::calcaulteRandomBetweenMinMax(2, 10);

            // Create the sale
            $sale = Sale::create([
                'gameweek' => $gameWeek,
                'timelimit_days' => $timelimit_days,

                'quantity' => $saleDemand,
                'sale_price' => $companyProduct->sale_price,
                'shipping_cost' => $wilayaShippingCost,
                'shipping_time_days' => $randomWilaya->real_shipping_time_days,

                'status' => Sale::STATUS_INITIATED,
                'initiated_at' => SettingsService::getCurrentTimestamp(),

                'company_id' => $company->id,
                'product_id' => $product->id,
                'wilaya_id' => $randomWilaya->id,
            ]);

            // Add to notification batch
            $salesGenerated[] = [
                'product' => $product,
                'quantity' => $saleDemand,
                'wilaya' => $randomWilaya->name,
            ];
        }

        // Create ONE notification for all generated sales
        if (!empty($salesGenerated)) {
            NotificationService::createBatchSalesInitiatedNotification($company, $salesGenerated);
        }
    }

    // Confirm a sale
    public static function confirmSale($sale){
        // Get current timestamp
        $confirmedAt = SettingsService::getCurrentTimestamp();

        $sale->update([
            'status' => Sale::STATUS_CONFIRMED,
            'confirmed_at' => $confirmedAt,
        ]);

        // Pay the sale shipping cost
        FinanceService::paySaleShippingCost($sale->company, $sale);

        // Update the inventory
        InventoryService::saleConfirmed($sale);
    }

    // Deliver a sale
    public static function processDeliveredSale($company){
        $sales = $company->sales()->where('status', Sale::STATUS_CONFIRMED)->get();

        foreach($sales as $sale){
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $deliveredAt = $sale->confirmed_at->copy()->addDays($sale->shipping_time_days);

            if($deliveredAt <= $currentTimestamp){
                $sale->update([
                    'status' => Sale::STATUS_DELIVERED,
                    'delivered_at' => $currentTimestamp,
                ]);

                // Receive the sale payment
                FinanceService::receiveSalePayment($sale->company, $sale);

                // Create notification
                NotificationService::createSaleDeliveredNotification($company, $sale, $sale->product, $sale->quantity);
            }
        }
    }

    // Cancel sales that have exceeded their time limit
    public static function cancelSales($company){
        $sales = $company->sales()->where('status', Sale::STATUS_INITIATED)->get();
        
        // Collect all cancelled sales for batched notification
        $cancelledSales = [];
        
        foreach($sales as $sale){
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $saleInitiatedAt = $sale->initiated_at;
            $timeLimitDays = $sale->timelimit_days;

            if($saleInitiatedAt->addDays($timeLimitDays) <= $currentTimestamp){
                $sale->update([
                    'status' => Sale::STATUS_CANCELLED,
                ]);

                // Add to notification batch
                $cancelledSales[] = [
                    'product' => $sale->product,
                    'quantity' => $sale->quantity,
                    'wilaya' => $sale->wilaya->name,
                ];
            }
        }

        // Create ONE notification for all cancelled sales
        if (!empty($cancelledSales)) {
            NotificationService::createBatchSalesCancelledNotification($company, $cancelledSales);
        }
    }
}