<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDemand;
use Illuminate\Database\Seeder;

class ProductDemandsSeeder extends Seeder
{
    public function run(): void
    {
        // Get cosmetics-themed products
        $baseOil = Product::where('name', 'Base Oil')->first();
        $activeIngredient = Product::where('name', 'Active Ingredient')->first();
        $faceCream = Product::where('name', 'Face Cream')->first();

        // Also get other products
        $steel = Product::where('name', 'Steel')->first();
        $motor = Product::where('name', 'Motor')->first();
        $eBike = Product::where('name', 'E-Bike')->first();

        $products = [
            $faceCream,
            $activeIngredient,
            $baseOil,
            $eBike,
            $motor,
            $steel
        ];

        // Create demand data for 52 weeks (1 year)
        foreach ($products as $product) {
            if (!$product) continue;

            // Determine demand range based on product type
            $minDemand = 0;
            $maxDemand = 0;
            $minPrice = 0;
            $maxPrice = 0;

            switch ($product->type) {
                case 'finished_product':
                    $minDemand = 100;
                    $maxDemand = 500;
                    $minPrice = 1500;
                    $maxPrice = 3000;
                    break;
                case 'component':
                    $minDemand = 50;
                    $maxDemand = 200;
                    $minPrice = 500;
                    $maxPrice = 1200;
                    break;
                case 'raw_material':
                    $minDemand = 20;
                    $maxDemand = 100;
                    $minPrice = 200;
                    $maxPrice = 800;
                    break;
            }

            // Create demands for weeks 1-52
            for ($week = 1; $week <= 52; $week++) {
                // Add some variation to make it interesting
                $weekMultiplier = 1 + ($week / 52 * 0.2); // Gradual increase over year
                $demandVariation = 0.8 + (rand(0, 40) / 100); // Random variation 80-120%
                
                $gameweekMinDemand = round($minDemand * $weekMultiplier * $demandVariation);
                $gameweekMaxDemand = round($maxDemand * $weekMultiplier * $demandVariation);
                $gameweekMinPrice = round($minPrice * (1 + ($week / 52 * 0.1)), 3);
                $gameweekMaxPrice = round($maxPrice * (1 + ($week / 52 * 0.1)), 3);

                ProductDemand::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'gameweek' => $week
                    ],
                    [
                        'min_demand' => $gameweekMinDemand,
                        'max_demand' => $gameweekMaxDemand,
                        'market_price' => round($gameweekMinPrice + (rand(0, ($gameweekMaxPrice - $gameweekMinPrice) * 1000)) / 1000, 3)
                    ]
                );
            }
        }
    }
}

