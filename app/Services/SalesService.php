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

        return ($productDemand) ? $productDemand->market_price : 0.000;
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

        foreach($companyProducts as $companyProduct){
            // Get the product demand for the current gameweek
            $product = $companyProduct->product;
            $productDemand = $product->demands()->where('gameweek', SettingsService::getCurrentGameWeek())->first();
            if(!$productDemand){
                continue;
            }

            // Calculate the price difference between the market price and the company product sale price
            $priceDifference = $productDemand->market_price - $companyProduct->sale_price;

            // Get the ad market impact percentage
            $adMarketImpactPercentage = AdsService::getAdMarketImpactPercentage($company, $product);

            // Calculate the demand for the week based on the price difference and the elasticity coefficient with marketing boost
            $baseDemand = $productDemand->real_demand * (1 + $adMarketImpactPercentage);
            $demandForWeek = $baseDemand - $product->elasticity_coefficient * $baseDemand * ($priceDifference / $productDemand->market_price);

            if($demandForWeek > $productDemand->max_demand){
                $demandForWeek = $productDemand->max_demand;
            }

            if($demandForWeek < 0){
                $demandForWeek = 0;
            }

            $numberOfSales = rand(1, 4);
            
            // Generate individual sale quantities with fluctuations
            $saleQuantities = [];
            $totalAllocated = 0;
            
            // Generate random weights for each sale to create fluctuations
            for($i = 0; $i < $numberOfSales; $i++){
                // Generate a random weight between 0.5 and 1.5 for each sale
                $weight = (rand(50, 150) / 100);
                $saleQuantities[] = $weight;
                $totalAllocated += $weight;
            }
            
            // Normalize the weights to ensure they sum to the daily demand
            for($i = 0; $i < $numberOfSales; $i++){
                $saleQuantities[$i] = ($saleQuantities[$i] / $totalAllocated) * $demandForWeek;
            }

            // Create the sales
            for($i = 0; $i < $numberOfSales; $i++){
                // Calculate the demand for each sale
                $saleDemand = round($saleQuantities[$i]);

                // Get a random wilaya
                $randomWilaya = Wilaya::inRandomOrder()->first();

                // Get the wilaya shipping cost
                $wilayaShippingCost = $randomWilaya->real_shipping_cost;

                // Get the current gameweek and the timelimit days
                // Get current timestamp
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
            }

            if($numberOfSales > 0){
                NotificationService::createSaleInitiatedNotification($company, $product, $numberOfSales);
            }
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
        
        foreach($sales as $sale){
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $saleInitiatedAt = $sale->initiated_at;
            $timeLimitDays = $sale->timelimit_days;

            if($saleInitiatedAt->addDays($timeLimitDays) <= $currentTimestamp){
                $sale->update([
                    'status' => Sale::STATUS_CANCELLED,
                ]);

                NotificationService::createSaleCancelledNotification($company, $sale, $sale->product, $sale->quantity);
            }
        }
    }
}