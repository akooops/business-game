<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['raw_material', 'component', 'finished_product']),
            'elasticity_coefficient' => fake()->randomFloat(3, 0.1, 2.0),
            'shelf_life_days' => fake()->optional()->numberBetween(30, 365),
            'has_expiration' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product has high elasticity.
     */
    public function highElasticity(): static
    {
        return $this->state(fn (array $attributes) => [
            'elasticity_coefficient' => fake()->randomFloat(2, 1.5, 3.0),
        ]);
    }
}
