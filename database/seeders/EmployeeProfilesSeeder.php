<?php

namespace Database\Seeders;

use App\Models\EmployeeProfile;
use Illuminate\Database\Seeder;

class EmployeeProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Operator',
                'description' => 'Machine operator',
                'min_salary_month' => 300,
                'avg_salary_month' => 400,
                'max_salary_month' => 600,
                'min_recruitment_cost' => 200,
                'avg_recruitment_cost' => 300,
                'max_recruitment_cost' => 500,
                'real_recruitment_cost' => 300,
            ],
            [
                'name' => 'Engineer',
                'description' => 'Production engineer',
                'min_salary_month' => 800,
                'avg_salary_month' => 1000,
                'max_salary_month' => 1500,
                'min_recruitment_cost' => 800,
                'avg_recruitment_cost' => 1200,
                'max_recruitment_cost' => 2000,
                'real_recruitment_cost' => 1200,
            ],
        ];

        foreach ($profiles as $data) {
            EmployeeProfile::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


