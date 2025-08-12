<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Algeria',
                'customs_duties_rate' => 0.250,
                'allows_imports' => true,
            ],
            [
                'name' => 'France',
                'customs_duties_rate' => 0.100,
                'allows_imports' => true,
            ],
        ];

        foreach ($countries as $data) {
            Country::updateOrCreate(
                ['name' => $data['name']],
                [
                    'customs_duties_rate' => $data['customs_duties_rate'],
                    'allows_imports' => $data['allows_imports'],
                ]
            );
        }
    }
}


