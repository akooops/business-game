<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Supplier;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        $algeria = Country::where('name', 'Algeria')->first();
        $france = Country::where('name', 'France')->first();
        $algiers = Wilaya::where('name', 'Algiers')->first();

        $suppliers = [
            [
                'name' => 'DZ Metals',
                'is_international' => false,
                'carbon_footprint' => 5,
                'wilaya_id' => optional($algiers)->id,
                'country_id' => null,
                'min_shipping_cost' => 20,
                'max_shipping_cost' => 45,
                'real_shipping_cost' => 30,
                'min_shipping_time_days' => 1,
                'max_shipping_time_days' => 3,
                'real_shipping_time_days' => 2,
            ],
            [
                'name' => 'FR Components',
                'is_international' => true,
                'carbon_footprint' => 10,
                'wilaya_id' => null,
                'country_id' => optional($france)->id,
                'min_shipping_cost' => 100,
                'max_shipping_cost' => 220,
                'real_shipping_cost' => 150,
                'min_shipping_time_days' => 3,
                'max_shipping_time_days' => 7,
                'real_shipping_time_days' => 5,
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


