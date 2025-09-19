<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeProfile>
 */
class EmployeeProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'description' => fake()->sentence(),
            'min_salary_month' => fake()->randomFloat(3, 2000, 4000),
            'max_salary_month' => fake()->randomFloat(3, 5000, 10000),
            'min_recruitment_cost' => fake()->randomFloat(3, 1000, 3000),
            'max_recruitment_cost' => fake()->randomFloat(3, 4000, 8000),
        ];
    }

    /**
     * Indicate that the profile is for high-level positions.
     */
    public function highLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_salary_month' => fake()->randomFloat(3, 8000, 15000),
            'max_salary_month' => fake()->randomFloat(3, 15000, 25000),
        ]);
    }

    /**
     * Indicate that the profile is for entry-level positions.
     */
    public function entryLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_salary_month' => fake()->randomFloat(3, 1500, 3000),
            'max_salary_month' => fake()->randomFloat(3, 3000, 5000),
        ]);
    }
}
