<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Bank',
            'loan_interest_rate' => fake()->randomFloat(3, 0.02, 0.15),
            'loan_duration_months' => fake()->randomElement([12, 24, 36, 48, 60]),
            'loan_max_amount' => fake()->randomFloat(3, 10000, 1000000),
        ];
    }
}
