<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyMachine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionOrder>
 */
class ProductionOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_machine_id' => CompanyMachine::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->randomFloat(3, 1, 1000),
            'time_to_complete' => fake()->numberBetween(1, 168), // 1 to 168 hours
            'quality_factor' => fake()->randomFloat(3, 0.5, 1.0),
            'employee_efficiency_factor' => fake()->randomFloat(3, 0.5, 1.0),
            'carbon_footprint' => fake()->randomFloat(3, 0, 100),
            'status' => 'in_progress',
        ];
    }

    /**
     * Indicate that the production order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the production order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subDays(1),
            'completed_at' => now(),
        ]);
    }
}
