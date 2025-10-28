<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AddProductsToCompanySeeder extends Seeder
{
    public function run(): void
    {
        // Get the player's company
        $company = Company::whereHas('user', function($query) {
            $query->where('email', 'player@example.com');
        })->first();

        if (!$company) {
            $this->command->error('Player company not found. Run CompaniesSeeder first.');
            return;
        }

        // Get all products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Run ProductsSeeder first.');
            return;
        }

        // Add products to company with inventory
        foreach ($products as $product) {
            CompanyProduct::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'product_id' => $product->id
                ],
                [
                    'available_stock' => rand(10, 100), // Random stock between 10 and 100
                    'sale_price' => rand(100, 500), // Random sale price between 100 and 500
                ]
            );
        }

        $this->command->info("Added all products to company with inventory.");
    }
}

