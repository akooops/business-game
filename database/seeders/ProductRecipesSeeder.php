<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductRecipe;
use Illuminate\Database\Seeder;

class ProductRecipesSeeder extends Seeder
{
    public function run(): void
    {
        $ebike = Product::where('name', 'E-Bike')->first();
        $motor = Product::where('name', 'Motor')->first();
        $steel = Product::where('name', 'Steel')->first();

        if ($ebike && $motor) {
            ProductRecipe::updateOrCreate(
                ['product_id' => $ebike->id, 'material_id' => $motor->id],
                ['quantity' => 1]
            );
        }

        if ($motor && $steel) {
            ProductRecipe::updateOrCreate(
                ['product_id' => $motor->id, 'material_id' => $steel->id],
                ['quantity' => 5]
            );
        }
    }
}


