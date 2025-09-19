<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertiser>
 */
class AdvertiserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'min_price' => fake()->randomFloat(3, 100, 1000),
            'max_price' => fake()->randomFloat(3, 1000, 5000),
            'real_price' => fake()->randomFloat(3, 100, 5000),
            'min_market_impact_percentage' => fake()->randomFloat(3, 0.01, 0.1),
            'max_market_impact_percentage' => fake()->randomFloat(3, 0.1, 0.5),
            'duration_days' => fake()->numberBetween(7, 30),
        ];
    }


}
