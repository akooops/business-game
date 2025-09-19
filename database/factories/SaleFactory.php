<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
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
            'product_id' => Product::factory(),
            'wilaya_id' => Wilaya::factory(),
            'quantity' => fake()->numberBetween(1, 1000),
            'sale_price' => fake()->randomFloat(3, 10, 1000),
            'shipping_cost' => fake()->randomFloat(3, 10, 100),
            'shipping_time_days' => fake()->numberBetween(1, 30),
            'status' => 'initiated',
            'timelimit_days' => fake()->numberBetween(1, 30),
            'initiated_at' => now(),
        ];
    }

    /**
     * Indicate that the sale is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the sale is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'confirmed_at' => now()->subDays(5),
            'delivered_at' => now(),
        ]);
    }

    /**
     * Indicate that the sale is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
