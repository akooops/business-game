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
                'max_salary_month' => 600,
                'min_recruitment_cost' => 200,
                'max_recruitment_cost' => 500,
            ],
            [
                'name' => 'Engineer',
                'description' => 'Production engineer',
                'min_salary_month' => 800,
                'max_salary_month' => 1500,
                'min_recruitment_cost' => 800,
                'max_recruitment_cost' => 2000,
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


