<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->randomFloat(3, 1, 1000),
            'sale_price' => fake()->randomFloat(3, 5, 500),
            'shipping_cost' => fake()->randomFloat(3, 10, 100),
            'customs_duties' => fake()->randomFloat(3, 0, 50),
            'total_cost' => fake()->randomFloat(3, 100, 10000),
            'carbon_footprint' => fake()->randomFloat(3, 0, 100),
            'shipping_time_days' => fake()->numberBetween(1, 30),
            'status' => 'ordered',
            'ordered_at' => now(),
        ];
    }

    /**
     * Indicate that the purchase is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the purchase is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
