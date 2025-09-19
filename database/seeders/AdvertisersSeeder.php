<?php

namespace Database\Seeders;

use App\Models\Advertiser;
use Illuminate\Database\Seeder;

class AdvertisersSeeder extends Seeder
{
    public function run(): void
    {
        $advertisers = [
            [
                'name' => 'Social Media Marketing Pro',
                'min_price' => 5000,
                'max_price' => 15000,
                'min_market_impact_percentage' => 0.15,
                'max_market_impact_percentage' => 0.25,
                'duration_days' => 7,
            ],
            [
                'name' => 'TV Commercial Master',
                'min_price' => 20000,
                'max_price' => 50000,
                'min_market_impact_percentage' => 0.30,
                'max_market_impact_percentage' => 0.45,
                'duration_days' => 14,
            ],
            [
                'name' => 'Radio Wave Marketing',
                'min_price' => 3000,
                'max_price' => 8000,
                'min_market_impact_percentage' => 0.10,
                'max_market_impact_percentage' => 0.20,
                'duration_days' => 5,
            ],
            [
                'name' => 'Digital Banner Expert',
                'min_price' => 2000,
                'max_price' => 6000,
                'min_market_impact_percentage' => 0.08,
                'max_market_impact_percentage' => 0.18,
                'duration_days' => 10,
            ],
            [
                'name' => 'Influencer Network',
                'min_price' => 8000,
                'max_price' => 25000,
                'min_market_impact_percentage' => 0.20,
                'max_market_impact_percentage' => 0.35,
                'duration_days' => 12,
            ],
            [
                'name' => 'Print Media Specialist',
                'min_price' => 4000,
                'max_price' => 12000,
                'min_market_impact_percentage' => 0.12,
                'max_market_impact_percentage' => 0.22,
                'duration_days' => 8,
            ],
        ];

        foreach ($advertisers as $advertiserData) {
            Advertiser::create($advertiserData);
        }
    }
}
