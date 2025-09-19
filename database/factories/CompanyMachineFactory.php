<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Machine;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyMachine>
 */
class CompanyMachineFactory extends Factory
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
            'machine_id' => Machine::factory(),
            'employee_id' => null,
            'speed' => fake()->randomFloat(3, 10, 30),
            'quality_factor' => fake()->randomFloat(3, 0.6, 1.0),
            'operations_cost' => fake()->randomFloat(3, 500, 2000),
            'carbon_footprint' => fake()->randomFloat(3, 10, 100),
            'reliability_decay_days' => fake()->randomFloat(3, 0.001, 0.01),
            'maintenance_cost' => fake()->randomFloat(3, 100, 1000),
            'maintenance_time_days' => fake()->numberBetween(1, 7),
            'current_reliability' => fake()->randomFloat(3, 0.5, 1.0),
            'loss_on_sale_days' => fake()->randomFloat(3, 0.001, 0.01),
            'acquisition_cost' => fake()->randomFloat(3, 10000, 100000),
            'current_value' => fake()->randomFloat(3, 10000, 100000),
            'status' => 'inactive',
            'setup_at' => now(),
        ];
    }

    /**
     * Indicate that the machine is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the machine is assigned to an employee.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => Employee::factory(),
            'status' => 'active',
        ]);
    }
}
