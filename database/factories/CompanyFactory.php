<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'funds' => fake()->randomFloat(3, 10000, 1000000),
            'research_level' => fake()->numberBetween(0, 10),
            'carbon_footprint' => fake()->randomFloat(3, 0, 1000),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
