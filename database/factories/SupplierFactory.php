<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
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
            'is_international' => fake()->boolean(),
            'min_shipping_cost' => fake()->randomFloat(3, 20, 100),
            'max_shipping_cost' => fake()->randomFloat(3, 100, 300),
            'real_shipping_cost' => fake()->randomFloat(3, 20, 300),
            'min_shipping_time_days' => fake()->numberBetween(2, 10),
            'max_shipping_time_days' => fake()->numberBetween(10, 30),
            'real_shipping_time_days' => fake()->numberBetween(2, 30),
            'carbon_footprint' => fake()->randomFloat(3, 0, 100),
            'country_id' => Country::factory(),
            'wilaya_id' => Wilaya::factory(),
        ];
    }

    /**
     * Indicate that the supplier is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    /**
     * Indicate that the supplier has low shipping costs.
     */
    public function lowCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_shipping_cost' => fake()->randomFloat(3, 5, 25),
            'max_shipping_cost' => fake()->randomFloat(3, 25, 50),
        ]);
    }

    /**
     * Indicate that the supplier has high shipping costs.
     */
    public function highCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_shipping_cost' => fake()->randomFloat(3, 200, 500),
            'max_shipping_cost' => fake()->randomFloat(3, 500, 1000),
        ]);
    }
}
