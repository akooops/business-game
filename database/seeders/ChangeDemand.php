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

        foreach ($demandsData as $productName => $demandRange) {
            // Find the product by name
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                $this->command->warn("Product not found: {$productName}");
                continue;
            }

            $this->command->info("Processing product: {$productName}");

            // Create demand data for weeks 1 to 52
            for ($week = 1; $week <= 52; $week++) {
                // Generate random demand within min-max range for this week
                // Using a weighted random approach that favors values closer to average
                $minDemand = $demandRange['min'];
                $maxDemand = $demandRange['max'];
                $avgDemand = $demandRange['avg'];

                // Add some seasonal variation (optional - peaks around week 26)
                $seasonalFactor = 1 + (0.15 * sin(2 * M_PI * $week / 52));
                
                // Apply seasonal factor to the range
                $adjustedMin = round($minDemand * $seasonalFactor);
                $adjustedMax = round($maxDemand * $seasonalFactor);

                // Ensure min is always less than max
                if ($adjustedMin >= $adjustedMax) {
                    $adjustedMax = $adjustedMin + 1;
                }

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

            $this->command->info("Completed 52 weeks of demand for: {$productName}");
        }

        $this->command->info('Product demands seeding completed!');
    }
}

