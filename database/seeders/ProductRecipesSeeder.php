<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductRecipe;
use Illuminate\Database\Seeder;

class ProductRecipesSeeder extends Seeder
{
    public function run(): void
    {
        $faceCream = Product::where('name', 'Face Cream')->first();
        $activeIngredient = Product::where('name', 'Active Ingredient')->first();
        $baseOil = Product::where('name', 'Base Oil')->first();

        if ($faceCream && $activeIngredient) {
            ProductRecipe::updateOrCreate(
                ['product_id' => $faceCream->id, 'material_id' => $activeIngredient->id],
                ['quantity' => 1]
            );
        }

        if ($activeIngredient && $baseOil) {
            ProductRecipe::updateOrCreate(
                ['product_id' => $activeIngredient->id, 'material_id' => $baseOil->id],
                ['quantity' => 5]
            );
        }
    }
}


