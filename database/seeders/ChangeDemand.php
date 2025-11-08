<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDemand;
use Illuminate\Database\Seeder;

class ChangeDemand extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Product demand data with min, avg, and max values
        $demandsData = [
            'Moisturizing Cream (120 mL)' => ['min' => 2763, 'avg' => 3070, 'max' => 3377],
            'Serum (30 mL)' => ['min' => 408, 'avg' => 453, 'max' => 498],
            'Face Scrub (150 mL)' => ['min' => 911, 'avg' => 1012, 'max' => 1113],
            'Shower Gel (250 ml)' => ['min' => 1958, 'avg' => 2176, 'max' => 2394],
            'Soap (100g)' => ['min' => 1171, 'avg' => 1301, 'max' => 1431],
            'Shampoo (250 mL)' => ['min' => 858, 'avg' => 953, 'max' => 1048],
            'Conditioner (250 mL)' => ['min' => 2763, 'avg' => 3070, 'max' => 3377],
            'Deodorant (200 mL)' => ['min' => 311, 'avg' => 346, 'max' => 381],
            'Liquid Foundation (30 mL)' => ['min' => 248, 'avg' => 276, 'max' => 304],
            'BB Cream (30 mL)' => ['min' => 726, 'avg' => 807, 'max' => 888],
            'Lip Balm (10 mL)' => ['min' => 1073, 'avg' => 1192, 'max' => 1311],
            'Mascara (100g)' => ['min' => 872, 'avg' => 969, 'max' => 1066],
            'Men\'s Perfume (100 mL)' => ['min' => 248, 'avg' => 276, 'max' => 304],
            'Women\'s Perfume (100 mL)' => ['min' => 224, 'avg' => 249, 'max' => 274],
        ];

        // Product market price data with min, avg, and max values
        $pricesData = [
            'Moisturizing Cream (120 mL)' => ['min' => 2300, 'avg' => 2530, 'max' => 2783],
            'Serum (30 mL)' => ['min' => 2100, 'avg' => 2310, 'max' => 2541],
            'Face Scrub (150 mL)' => ['min' => 2000, 'avg' => 2200, 'max' => 2420],
            'Shower Gel (250 ml)' => ['min' => 2200, 'avg' => 2420, 'max' => 2662],
            'Soap (100g)' => ['min' => 1810, 'avg' => 1991, 'max' => 2190.1],
            'Shampoo (250 mL)' => ['min' => 1800, 'avg' => 1980, 'max' => 2178],
            'Conditioner (250 mL)' => ['min' => 2000, 'avg' => 2200, 'max' => 2420],
            'Deodorant (200 mL)' => ['min' => 2200, 'avg' => 2420, 'max' => 2662],
            'Liquid Foundation (30 mL)' => ['min' => 2400, 'avg' => 2640, 'max' => 2904],
            'BB Cream (30 mL)' => ['min' => 4000, 'avg' => 4400, 'max' => 4840],
            'Lip Balm (10 mL)' => ['min' => 1310, 'avg' => 1441, 'max' => 1585.1],
            'Mascara (100g)' => ['min' => 2000, 'avg' => 2200, 'max' => 2420],
            'Men\'s Perfume (100 mL)' => ['min' => 14800, 'avg' => 16280, 'max' => 17908],
            'Women\'s Perfume (100 mL)' => ['min' => 12200, 'avg' => 13420, 'max' => 14762],
        ];

        // Assign unique demand patterns to each product (smoothed)
        $productPatterns = [
            'Moisturizing Cream (120 mL)' => ['type' => 'seasonal_dual', 'volatility' => 'medium', 'trend' => 'upward'],
            'Serum (30 mL)' => ['type' => 'growing', 'volatility' => 'medium', 'trend' => 'upward'],
            'Face Scrub (150 mL)' => ['type' => 'summer_peak', 'volatility' => 'low', 'trend' => 'stable'],
            'Shower Gel (250 ml)' => ['type' => 'steady', 'volatility' => 'medium', 'trend' => 'upward'],
            'Soap (100g)' => ['type' => 'spring_peak', 'volatility' => 'medium', 'trend' => 'stable'],
            'Shampoo (250 mL)' => ['type' => 'spring_peak', 'volatility' => 'medium', 'trend' => 'downward'],
            'Conditioner (250 mL)' => ['type' => 'steady', 'volatility' => 'medium', 'trend' => 'upward'],
            'Deodorant (200 mL)' => ['type' => 'summer_peak', 'volatility' => 'medium', 'trend' => 'stable'],
            'Liquid Foundation (30 mL)' => ['type' => 'seasonal_dual', 'volatility' => 'low', 'trend' => 'downward'],
            'BB Cream (30 mL)' => ['type' => 'spring_peak', 'volatility' => 'low', 'trend' => 'upward'],
            'Lip Balm (10 mL)' => ['type' => 'winter_peak', 'volatility' => 'medium', 'trend' => 'stable'],
            'Mascara (100g)' => ['type' => 'steady', 'volatility' => 'low', 'trend' => 'downward'],
            'Men\'s Perfume (100 mL)' => ['type' => 'seasonal_dual', 'volatility' => 'medium', 'trend' => 'stable'],
            'Women\'s Perfume (100 mL)' => ['type' => 'holiday_spikes', 'volatility' => 'low', 'trend' => 'upward'],
        ];

        foreach ($demandsData as $productName => $demandRange) {
            // Find the product by name
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                $this->command->warn("Product not found: {$productName}");
                continue;
            }

            $this->command->info("Processing product: {$productName}");
            
            $pattern = $productPatterns[$productName];

            // Get price data for this product
            $priceData = $pricesData[$productName] ?? null;

            $demandReductionFactor = 0.94;
            $trendDirection = 1;
            $trendWindowRemaining = 0;

            // Create demand data for weeks 1 to 52
            for ($week = 1; $week <= 52; $week++) {
                if ($trendWindowRemaining <= 0) {
                    $trendWindowRemaining = mt_rand(1, 5);
                    if ($week > 1) {
                        $trendDirection *= -1;
                    }
                }
                $trendWindowRemaining--;

                $minDemand = round($demandRange['min'] * $demandReductionFactor);
                $maxDemand = round($demandRange['max'] * $demandReductionFactor);

                // 1. Calculate trend factor (upward, downward, or stable)
                $trendFactor = 1.0;
                switch ($pattern['trend']) {
                    case 'upward':
                        $trendGrowth = ($week / 52 * 0.10);
                        $trendFactor = 1 + ($trendDirection === 1 ? $trendGrowth : -0.6 * $trendGrowth);
                        break;
                    case 'downward':
                        $trendGrowth = ($week / 52 * -0.11);
                        $trendFactor = 1 + ($trendDirection === 1 ? $trendGrowth : -0.6 * $trendGrowth);
                        break;
                    case 'stable':
                        $trendFactor = 1.0;
                        break;
                }

                // 2. Calculate seasonal factor based on pattern type
                $seasonalFactor = 1.0;
                switch ($pattern['type']) {
                    case 'summer_peak':
                        // Peaks around week 26 (mid-year)
                        $seasonalFactor = 1 + (0.15 * sin(2 * M_PI * ($week - 13) / 52));
                        break;
                    case 'winter_peak':
                        // Peaks around week 1 and 52 (winter)
                        $seasonalFactor = 1 + (0.15 * cos(2 * M_PI * $week / 52));
                        break;
                    case 'spring_peak':
                        // Peaks around week 13 (spring)
                        $seasonalFactor = 1 + (0.12 * sin(2 * M_PI * ($week - 26) / 52));
                        break;
                    case 'seasonal_dual':
                        // Two peaks per year (spring & fall)
                        $seasonalFactor = 1 + (0.10 * sin(4 * M_PI * $week / 52));
                        break;
                    case 'holiday_spikes':
                        // Spikes around specific weeks (holidays)
                        $isHolidayWeek = in_array($week, [1, 12, 24, 48, 52]);
                        $seasonalFactor = $isHolidayWeek ? 1.20 : 0.95;
                        break;
                    case 'steady':
                        // Very minimal variation
                        $seasonalFactor = 1 + (mt_rand(-3, 3) / 100);
                        break;
                    case 'growing':
                        // Growing pattern - similar to steady but with upward trend
                        $seasonalFactor = 1 + (mt_rand(-3, 3) / 100);
                        break;
                    default:
                        // Default to steady pattern for unknown types
                        $seasonalFactor = 1 + (mt_rand(-3, 3) / 100);
                        break;
                }

                // 3. Add volatility (random noise)
                $volatilityFactor = 1.0;
                switch ($pattern['volatility']) {
                    case 'medium':
                        $volatilityFactor = 1 + (mt_rand(-10, 10) / 100);
                        break;
                    case 'low':
                        $volatilityFactor = 1 + (mt_rand(-5, 5) / 100);
                        break;
                }

                // Additional demand swing to keep things unpredictable (-20% to +5%)
                $volatilityFactor *= 1 + (mt_rand(-20, 5) / 100);

                // 4. Apply all factors for demand
                $combinedFactor = $trendFactor * $seasonalFactor * $volatilityFactor;
                
                $adjustedMin = round($minDemand * $combinedFactor);
                $adjustedMax = round($maxDemand * $combinedFactor);

                // Ensure min is always less than max
                if ($adjustedMin >= $adjustedMax) {
                    $adjustedMax = $adjustedMin + 1;
                }

                // Ensure values don't go negative
                $adjustedMin = max(1, $adjustedMin);
                $adjustedMax = max(2, $adjustedMax);

                // 5. Calculate market price with variation (inverse relationship with demand)
                // When demand is high, prices tend to be lower, and vice versa
                $marketPrice = null;
                if ($priceData) {
                    $minPrice = $priceData['min'];
                    $avgPrice = $priceData['avg'];
                    $maxPrice = $priceData['max'];
                    
                    // Use inverse seasonal factor for price (opposite of demand)
                    // But keep trend and volatility similar
                    $priceSeasonalFactor = 1.0;
                    switch ($pattern['type']) {
                        case 'summer_peak':
                            // Prices lower in summer (demand higher)
                            $priceSeasonalFactor = 1 - (0.10 * sin(2 * M_PI * ($week - 13) / 52));
                            break;
                        case 'winter_peak':
                            // Prices higher in winter (demand higher)
                            $priceSeasonalFactor = 1 - (0.10 * cos(2 * M_PI * $week / 52));
                            break;
                        case 'spring_peak':
                            // Prices lower in spring (demand higher)
                            $priceSeasonalFactor = 1 - (0.08 * sin(2 * M_PI * ($week - 26) / 52));
                            break;
                        case 'seasonal_dual':
                            // Two cycles per year
                            $priceSeasonalFactor = 1 - (0.08 * sin(4 * M_PI * $week / 52));
                            break;
                        case 'holiday_spikes':
                            // Prices spike during holidays (demand spikes)
                            $isHolidayWeek = in_array($week, [1, 12, 24, 48, 52]);
                            $priceSeasonalFactor = $isHolidayWeek ? 1.15 : 1.0;
                            break;
                        case 'steady':
                            // Very minimal variation
                            $priceSeasonalFactor = 1 + (mt_rand(-2, 2) / 100);
                            break;
                        case 'growing':
                            // Growing pattern - similar to steady
                            $priceSeasonalFactor = 1 + (mt_rand(-2, 2) / 100);
                            break;
                        default:
                            // Default to steady pattern for unknown types
                            $priceSeasonalFactor = 1 + (mt_rand(-2, 2) / 100);
                            break;
                    }
                    
                    // Price trend (opposite of demand trend for some products)
                    $priceTrendFactor = 1.0;
                    switch ($pattern['trend']) {
                        case 'upward':
                            // Slight price increase but slower than demand
                            $priceTrendFactor = 1 + ($week / 52 * 0.05);
                            break;
                        case 'downward':
                            // Prices may increase as demand decreases
                            $priceTrendFactor = 1 + ($week / 52 * 0.03);
                            break;
                        case 'stable':
                            $priceTrendFactor = 1.0;
                            break;
                    }
                    
                    // Price volatility (similar to demand)
                    $priceVolatilityFactor = 1.0;
                    switch ($pattern['volatility']) {
                        case 'medium':
                            $priceVolatilityFactor = 1 + (mt_rand(-8, 8) / 100);
                            break;
                        case 'low':
                            $priceVolatilityFactor = 1 + (mt_rand(-4, 4) / 100);
                            break;
                    }
                    
                    // Combine factors for price
                    $priceCombinedFactor = $priceTrendFactor * $priceSeasonalFactor * $priceVolatilityFactor;
                    
                    // Calculate market price (between min and max based on avg)
                    $basePrice = $avgPrice * $priceCombinedFactor;

                    // Apply additional downward pressure on price (2% to 7%)
                    $basePrice *= 1 - (mt_rand(1, 2) / 100);
                    
                    // Ensure price stays within reasonable bounds
                    $marketPrice = max($minPrice * 0.9, min($maxPrice * 1.1, $basePrice));
                    $marketPrice = round($marketPrice, 2);
                } else {
                    // Fallback to product's avg_market_price if no price data
                    $marketPrice = $product->avg_market_price ?? 0;
                }

                ProductDemand::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'gameweek' => $week,
                    ],
                    [
                        'min_demand' => $adjustedMin,
                        'max_demand' => $adjustedMax,
                        'market_price' => $marketPrice,
                    ]
                );
            }

            $this->command->info("Completed 52 weeks of demand and market price data for: {$productName} (Pattern: {$pattern['type']}, Trend: {$pattern['trend']})");
        }

        $this->adjustRawMaterialBaseMetrics();

        $this->command->info('Product demands and market prices seeding completed!');
    }

    private function adjustRawMaterialBaseMetrics(): void
    {
        Product::where('type', Product::TYPE_RAW_MATERIAL)
            ->where('is_saleable', true)
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $demandFactor = 0.94 * (1 + mt_rand(-20, 5) / 100);
                    $priceFactor = 1 - (mt_rand(1, 2) / 100);

                    $newAvgDemand = max(1, round(($product->avg_demand ?? 0) * $demandFactor));
                    $newAvgMarketPrice = round(($product->avg_market_price ?? 0) * $priceFactor, 2);

                    $product->updateQuietly([
                        'avg_demand' => $newAvgDemand,
                        'avg_market_price' => $newAvgMarketPrice,
                    ]);

                    $this->command?->line(
                        sprintf(
                            "Raw material %s (ID %d) adjusted: avg_demand=%d, avg_market_price=%.2f",
                            $product->name,
                            $product->id,
                            $newAvgDemand,
                            $newAvgMarketPrice
                        )
                    );
                }
            });
    }
}

