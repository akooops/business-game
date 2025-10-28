<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
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
            // keep legacy name for compatibility, but fill firstname/lastname too
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'name' => function (array $attrs) {
                $first = $attrs['firstname'] ?? '';
                $last = $attrs['lastname'] ?? '';
                $full = trim($first . ' ' . $last);
                return $full !== '' ? $full : fake()->name();
            },
            'salary_month' => fake()->randomFloat(3, 2000, 8000),
            'recruitment_cost' => fake()->randomFloat(3, 1000, 5000),
            'current_mood' => fake()->randomFloat(3, 0.3, 1.0),
            'mood_decay_rate_days' => fake()->randomFloat(3, 0.001, 0.01),
            'efficiency_factor' => fake()->randomFloat(3, 0.5, 1.0),
            'status' => fake()->randomElement(['active', 'fired', 'resigned', 'applied']),
            'timelimit_days' => fake()->numberBetween(1, 30),
            'applied_at' => now(),
            'employee_profile_id' => \App\Models\EmployeeProfile::factory(),
        ];
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the employee is highly efficient.
     */
    public function highEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency' => fake()->randomFloat(2, 0.8, 1.0),
        ]);
    }

    /**
     * Indicate that the employee is experienced.
     */
    public function experienced(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => fake()->numberBetween(5, 15),
        ]);
    }
}
