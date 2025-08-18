<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Technology;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $techBasic = Technology::where('name', 'Basic Assembly')->first();

        $products = [
            [
                'name' => 'Steel',
                'description' => 'Raw steel material',
                'type' => Product::TYPE_RAW_MATERIAL,
                'elasticity_coefficient' => 1.000,
                'storage_cost' => 0.500,
                'measurement_unit' => 'kg',
                'need_technology' => false,
                'technology_id' => null,
            ],
            [
                'name' => 'Motor',
                'description' => 'Electric motor component',
                'type' => Product::TYPE_COMPONENT,
                'elasticity_coefficient' => 1.000,
                'storage_cost' => 1.000,
                'measurement_unit' => 'unit',
                'need_technology' => true,
                'technology_id' => optional($techBasic)->id,
            ],
            [
                'name' => 'E-Bike',
                'description' => 'Finished e-bike',
                'type' => Product::TYPE_FINISHED_PRODUCT,
                'elasticity_coefficient' => 1.200,
                'storage_cost' => 2.000,
                'measurement_unit' => 'unit',
                'need_technology' => true,
                'technology_id' => optional($techBasic)->id,
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


