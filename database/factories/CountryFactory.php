<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'customs_duties_rate' => fake()->randomFloat(3, 0.05, 0.50),
            'allows_imports' => fake()->boolean(80), // 80% chance of allowing imports
        ];
    }

    /**
     * Indicate that the country allows imports.
     */
    public function allowsImports(): static
    {
        return $this->state(fn (array $attributes) => [
            'allows_imports' => true,
        ]);
    }

    /**
     * Indicate that the country blocks imports.
     */
    public function blocksImports(): static
    {
        return $this->state(fn (array $attributes) => [
            'allows_imports' => false,
        ]);
    }

    /**
     * Indicate that the country has high customs duties.
     */
    public function highDuties(): static
    {
        return $this->state(fn (array $attributes) => [
            'customs_duties_rate' => fake()->randomFloat(3, 0.30, 0.60),
        ]);
    }
}
