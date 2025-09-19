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
                'max_shipping_cost' => 200,
                'real_shipping_cost' => 150,
                'min_shipping_time_days' => 1,
                'max_shipping_time_days' => 3,
                'real_shipping_time_days' => 2,
            ],
            [
                'name' => 'Oran',
                'min_shipping_cost' => 120,
                'max_shipping_cost' => 230,
                'real_shipping_cost' => 170,
                'min_shipping_time_days' => 1,
                'max_shipping_time_days' => 4,
                'real_shipping_time_days' => 2,
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


