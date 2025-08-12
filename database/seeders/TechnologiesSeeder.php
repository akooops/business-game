<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;

class TechnologiesSeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['name' => 'Basic Assembly', 'description' => 'Foundational assembly processes', 'level' => 1, 'research_cost' => 1000, 'research_time_days' => 7],
            ['name' => 'Quality Control', 'description' => 'Improved QA workflow', 'level' => 1, 'research_cost' => 1500, 'research_time_days' => 10],
            ['name' => 'Advanced Automation', 'description' => 'Automated production lines', 'level' => 2, 'research_cost' => 5000, 'research_time_days' => 21],
        ];

        foreach ($technologies as $data) {
            Technology::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


