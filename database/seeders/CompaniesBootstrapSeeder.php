<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CompaniesBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        // Give initial catalog access
        $productIds = Product::pluck('id')->all();
        foreach ($productIds as $pid) {
            CompanyProduct::updateOrCreate(
                ['company_id' => $company->id, 'product_id' => $pid],
                ['available_stock' => 0, 'sale_price' => 0]
            );
        }
    }
}


