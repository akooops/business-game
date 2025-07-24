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

    // Create a new sale
    public static function generateDemand($company)
    {
        $companyProducts = $company->companyProducts()->get();

        foreach($companyProducts as $companyProduct){
            $product = $companyProduct->product;
            $productDemand = $product->demands()->where('gameweek', SettingsService::getCurrentGameWeek())->first();

            if(!$productDemand){
                continue;
            }

            $priceDifference = $productDemand->market_price - $companyProduct->sale_price;

            // Later we should add marketing efforts to the demand formula
            $demandForWeek = $productDemand->real_demand - $product->elasticity_coefficient * $productDemand->real_demand * ($priceDifference / $productDemand->market_price);

            if($demandForWeek > $productDemand->max_demand){
                $demandForWeek = $productDemand->max_demand;
            }

            if($demandForWeek <= 0){
                $demandForWeek = 0;
            }

            $weekSalesQuantity = $company->sales()->where('product_id', $product->id)->where('gameweek', SettingsService::getCurrentGameWeek())->sum('quantity');

            $demandForWeek = $demandForWeek - $weekSalesQuantity;

            // Calculate base daily demand
            $baseDailyDemand = $demandForWeek / 7;
            
            $dailyDemand = CalculationsService::calculatePertValue($baseDailyDemand * 0.9, $baseDailyDemand, $baseDailyDemand * 1.1);

            // Generate the number of sales
            $numberOfSales = rand(0, 3);
            
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
                $saleQuantities[$i] = ($saleQuantities[$i] / $totalAllocated) * $dailyDemand;
            }
            
            // Create the sales
            for($i = 0; $i < $numberOfSales; $i++){
                $saleDemand = round($saleQuantities[$i]);

                $randomWilaya = Wilaya::inRandomOrder()->first();

                $wilayaShippingCost = $randomWilaya->real_shipping_cost;

                // Get current timestamp
                $gameWeek = SettingsService::getCurrentGameWeek();
                $timelimit_days = CalculationsService::calculatePertValue(2, 5, 12);

                $company->sales()->create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'wilaya_id' => $randomWilaya->id,

                    'quantity' => $saleDemand,
                    'sale_price' => $companyProduct->sale_price,

                    'shipping_cost' => $wilayaShippingCost,
                    'shipping_time_days' => $randomWilaya->avg_shipping_time_days,

                    'status' => Sale::STATUS_INITIATED,

                    'gameweek' => $gameWeek,
                    'timelimit_days' => $timelimit_days,

                    'initiated_at' => SettingsService::getCurrentTimestamp(),
                ]);
            }

            if($numberOfSales > 0){
                NotificationService::createSaleInitiatedNotification($company, $product, $numberOfSales);
            }
        }
    }

    public static function confirmSale($sale){
        // Get current timestamp
        $confirmedAt = SettingsService::getCurrentTimestamp();
        $estimatedArrivalAt = $confirmedAt->copy()->addDays($sale->shipping_time_days);

        // Calculate real delivery date
        $wilayaShippingTimeDays = CalculationsService::calculatePertValue($sale->wilaya->min_shipping_time_days, $sale->wilaya->avg_shipping_time_days, $sale->wilaya->max_shipping_time_days);
        $realDeliveredAt = $confirmedAt->copy()->addDays($wilayaShippingTimeDays);

        $sale->update([
            'status' => 'confirmed',
            'confirmed_at' => $confirmedAt,
            'estimated_delivered_at' => $estimatedArrivalAt,
            'real_delivered_at' => $realDeliveredAt,
        ]);

        FinanceService::paySaleShippingCost($sale->company, $sale);
        InventoryService::saleConfirmed($sale);
    }

    public static function deliveredSale($sale){
        $sale->update([
            'status' => 'delivered',
            'delivered_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::receiveSalePayment($sale->company, $sale);
        NotificationService::createSaleDeliveredNotification($sale);
    }

    public static function validateSaleConfirmation($sale){
        $errors = [];

        $shippingCost = $sale->shipping_cost;
        $totalCost = $shippingCost;

        if(!InventoryService::haveSufficientStock($sale->company, $sale->product, $sale->quantity)){
            $errors['stock'] = 'This company does not have enough stock of this product.';
        }

        if(!FinanceService::haveSufficientFunds($sale->company, $totalCost)){
            $errors['funds'] = 'This company does not have enough funds to confirm this sale shipping cost.';
        }

        if($sale->status != Sale::STATUS_INITIATED){
            $errors['status'] = 'This sale is not available for confirmation.';
        }

        $companyProduct = $sale->company->companyProducts()->where('product_id', $sale->product->id)->first();

        if(!$companyProduct) {
            $errors['company_product'] = 'This company does not sell this product.';
        }
        
        return $errors;
    }
}