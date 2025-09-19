<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'amount' => fake()->randomFloat(3, 100, 10000),
            'type' => fake()->randomElement(['sale', 'purchase', 'loan', 'salary', 'maintenance']),
            'description' => fake()->sentence(),
            'transaction_date' => now(),
        ];
    }

    /**
     * Indicate that the transaction is a sale.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sale',
            'amount' => fake()->randomFloat(3, 1000, 10000),
        ]);
    }

    /**
     * Indicate that the transaction is a purchase.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase',
            'amount' => fake()->randomFloat(3, 100, 5000),
        ]);
    }

    /**
     * Indicate that the transaction is a loan.
     */
    public function loan(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'loan',
            'amount' => fake()->randomFloat(3, 5000, 50000),
        ]);
    }
}
