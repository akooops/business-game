<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Machine>
 */
class MachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Production Line A', 'Assembly Machine', 'Packaging Unit', 'Quality Control System', 'Warehouse Robot']),
            'model' => fake()->randomElement(['Model X', 'Model Y', 'Model Z', 'Premium', 'Standard']),
            'manufacturer' => fake()->company(),
            'cost_to_acquire' => fake()->randomFloat(3, 10000, 100000),
            'operations_cost' => fake()->randomFloat(3, 500, 2000),
            'carbon_footprint' => fake()->randomFloat(3, 10, 100),
            'quality_factor' => fake()->randomFloat(3, 0.6, 1.0),
            'min_speed' => fake()->randomFloat(3, 5, 15),
            'max_speed' => fake()->randomFloat(3, 20, 40),
            'reliability_decay_days' => fake()->randomFloat(3, 0.001, 0.01),
            'min_maintenance_cost' => fake()->randomFloat(3, 100, 500),
            'max_maintenance_cost' => fake()->randomFloat(3, 600, 1000),
            'min_maintenance_time_days' => fake()->numberBetween(1, 3),
            'max_maintenance_time_days' => fake()->numberBetween(4, 7),
            'employee_profile_id' => \App\Models\EmployeeProfile::factory(),
        ];
    }

    /**
     * Indicate that the machine is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the machine is high quality.
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_factor' => fake()->randomFloat(2, 0.9, 1.0),
        ]);
    }

    /**
     * Indicate that the machine is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_to_acquire' => fake()->numberBetween(100000, 500000),
        ]);
    }
}
