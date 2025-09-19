<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Technology>
 */
class TechnologyFactory extends Factory
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
            'level' => fake()->numberBetween(0, 10),
            'research_cost' => fake()->randomFloat(3, 10000, 100000),
            'research_time_days' => fake()->numberBetween(1, 30),
        ];
    }

    /**
     * Indicate that the technology is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the technology is expensive to research.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'research_cost' => fake()->randomFloat(3, 100000, 500000),
        ]);
    }
}
