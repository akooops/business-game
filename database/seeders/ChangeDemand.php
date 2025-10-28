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

        // Assign unique demand patterns to each product (smoothed)
        $productPatterns = [
            'Moisturizing Cream (120 mL)' => ['type' => 'winter_peak', 'volatility' => 'low', 'trend' => 'stable'],
            'Serum (30 mL)' => ['type' => 'growing', 'volatility' => 'medium', 'trend' => 'upward'],
            'Face Scrub (150 mL)' => ['type' => 'summer_peak', 'volatility' => 'low', 'trend' => 'stable'],
            'Shower Gel (250 ml)' => ['type' => 'summer_peak', 'volatility' => 'low', 'trend' => 'stable'],
            'Soap (100g)' => ['type' => 'steady', 'volatility' => 'low', 'trend' => 'downward'],
            'Shampoo (250 mL)' => ['type' => 'steady', 'volatility' => 'medium', 'trend' => 'stable'],
            'Conditioner (250 mL)' => ['type' => 'winter_peak', 'volatility' => 'low', 'trend' => 'upward'],
            'Deodorant (200 mL)' => ['type' => 'summer_peak', 'volatility' => 'medium', 'trend' => 'stable'],
            'Liquid Foundation (30 mL)' => ['type' => 'seasonal_dual', 'volatility' => 'low', 'trend' => 'stable'],
            'BB Cream (30 mL)' => ['type' => 'spring_peak', 'volatility' => 'low', 'trend' => 'upward'],
            'Lip Balm (10 mL)' => ['type' => 'winter_peak', 'volatility' => 'medium', 'trend' => 'stable'],
            'Mascara (100g)' => ['type' => 'steady', 'volatility' => 'low', 'trend' => 'stable'],
            'Men\'s Perfume (100 mL)' => ['type' => 'holiday_spikes', 'volatility' => 'low', 'trend' => 'downward'],
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

            // Create demand data for weeks 1 to 52
            for ($week = 1; $week <= 52; $week++) {
                $minDemand = $demandRange['min'];
                $maxDemand = $demandRange['max'];

                // 1. Calculate trend factor (upward, downward, or stable)
                $trendFactor = 1.0;
                switch ($pattern['trend']) {
                    case 'upward':
                        $trendFactor = 1 + ($week / 52 * 0.12); // 12% growth over year
                        break;
                    case 'downward':
                        $trendFactor = 1 - ($week / 52 * 0.08); // 8% decline over year
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

                // 4. Apply all factors
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

                ProductDemand::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'gameweek' => $week,
                    ],
                    [
                        'min_demand' => $adjustedMin,
                        'max_demand' => $adjustedMax,
                    ]
                );
            }

            $this->command->info("Completed 52 weeks of demand for: {$productName} (Pattern: {$pattern['type']}, Trend: {$pattern['trend']})");
        }

        $this->command->info('Product demands seeding completed!');
    }
}

