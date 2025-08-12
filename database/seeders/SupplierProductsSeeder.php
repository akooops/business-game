<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Database\Seeder;

class SupplierProductsSeeder extends Seeder
{
    public function run(): void
    {
        $steel = Product::where('name', 'Steel')->first();
        $motor = Product::where('name', 'Motor')->first();

        $dz = Supplier::where('name', 'DZ Metals')->first();
        $fr = Supplier::where('name', 'FR Components')->first();

        if ($dz && $steel) {
            SupplierProduct::updateOrCreate(
                ['supplier_id' => $dz->id, 'product_id' => $steel->id],
                [
                    'min_sale_price' => 5,
                    'avg_sale_price' => 7,
                    'max_sale_price' => 10,
                    'real_sale_price' => 7,
                ]
            );
        }

        if ($fr && $motor) {
            SupplierProduct::updateOrCreate(
                ['supplier_id' => $fr->id, 'product_id' => $motor->id],
                [
                    'min_sale_price' => 40,
                    'avg_sale_price' => 55,
                    'max_sale_price' => 80,
                    'real_sale_price' => 55,
                ]
            );
        }
    }
}


