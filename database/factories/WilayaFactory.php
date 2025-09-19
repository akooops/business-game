<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wilaya>
 */
class WilayaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'min_shipping_cost' => fake()->randomFloat(3, 10, 100),
            'max_shipping_cost' => fake()->randomFloat(3, 100, 500),
            'real_shipping_cost' => fake()->randomFloat(3, 10, 500),
            'min_shipping_time_days' => fake()->numberBetween(1, 7),
            'max_shipping_time_days' => fake()->numberBetween(7, 21),
            'real_shipping_time_days' => fake()->numberBetween(1, 21),
        ];
    }

    /**
     * Indicate that the wilaya is nearby (low shipping costs).
     */
    public function nearby(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_shipping_cost' => fake()->randomFloat(3, 5, 25),
            'max_shipping_cost' => fake()->randomFloat(3, 25, 50),
            'min_shipping_time_days' => fake()->numberBetween(1, 3),
            'max_shipping_time_days' => fake()->numberBetween(3, 7),
        ]);
    }

    /**
     * Indicate that the wilaya is far away (high shipping costs).
     */
    public function farAway(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_shipping_cost' => fake()->randomFloat(3, 200, 500),
            'max_shipping_cost' => fake()->randomFloat(3, 500, 1000),
            'min_shipping_time_days' => fake()->numberBetween(14, 30),
            'max_shipping_time_days' => fake()->numberBetween(30, 60),
        ]);
    }
}
