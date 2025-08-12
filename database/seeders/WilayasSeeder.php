<?php

namespace Database\Seeders;

use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class WilayasSeeder extends Seeder
{
    public function run(): void
    {
        $wilayas = [
            [
                'name' => 'Algiers',
                'min_shipping_cost' => 100,
                'avg_shipping_cost' => 150,
                'max_shipping_cost' => 200,
                'real_shipping_cost' => 150,
                'min_shipping_time_days' => 1,
                'avg_shipping_time_days' => 2,
                'max_shipping_time_days' => 3,
            ],
            [
                'name' => 'Oran',
                'min_shipping_cost' => 120,
                'avg_shipping_cost' => 170,
                'max_shipping_cost' => 230,
                'real_shipping_cost' => 170,
                'min_shipping_time_days' => 1,
                'avg_shipping_time_days' => 2,
                'max_shipping_time_days' => 4,
            ],
        ];

        foreach ($wilayas as $data) {
            Wilaya::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


