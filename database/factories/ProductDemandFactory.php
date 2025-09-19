<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDemand>
 */
class ProductDemandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'gameweek' => fake()->numberBetween(1, 52),
            'market_price' => fake()->randomFloat(3, 10, 1000),
            'real_demand' => fake()->numberBetween(100, 10000),
            'max_demand' => fake()->numberBetween(1000, 20000),
        ];
    }

    /**
     * Indicate that the demand is high.
     */
    public function highDemand(): static
    {
        return $this->state(fn (array $attributes) => [
            'real_demand' => fake()->numberBetween(8000, 15000),
            'max_demand' => fake()->numberBetween(15000, 25000),
        ]);
    }

    /**
     * Indicate that the demand is low.
     */
    public function lowDemand(): static
    {
        return $this->state(fn (array $attributes) => [
            'real_demand' => fake()->numberBetween(100, 1000),
            'max_demand' => fake()->numberBetween(1000, 5000),
        ]);
    }
}
