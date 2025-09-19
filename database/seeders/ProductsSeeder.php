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
                'type' => 'raw_material',
                'elasticity_coefficient' => 1.000,
                'technology_id' => null,
            ],
            [
                'name' => 'Motor',
                'description' => 'Electric motor component',
                'type' => 'component',
                'elasticity_coefficient' => 1.000,
                'technology_id' => optional($techBasic)->id,
            ],
            [
                'name' => 'E-Bike',
                'description' => 'Finished e-bike',
                'type' => 'finished_product',
                'elasticity_coefficient' => 1.200,
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


