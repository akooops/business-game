<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Advertiser;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ad>
 */
class AdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'advertiser_id' => Advertiser::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->randomFloat(3, 100, 1000),
            'duration_days' => fake()->numberBetween(7, 30),
            'market_impact_percentage' => fake()->randomFloat(3, 0.1, 0.5),
            'status' => 'active',
            'started_at' => now(),
        ];
    }

    /**
     * Indicate that the ad is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the ad has high market impact.
     */
    public function highImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'market_impact_percentage' => fake()->randomFloat(3, 0.5, 1.0),
        ]);
    }
}
