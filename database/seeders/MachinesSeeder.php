<?php

namespace Database\Seeders;

use App\Models\EmployeeProfile;
use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachinesSeeder extends Seeder
{
    public function run(): void
    {
        $operator = EmployeeProfile::where('name', 'Operator')->first();

        $machines = [
            [
                'name' => 'Lathe',
                'model' => 'L-100',
                'manufacturer' => 'Machina',
                'cost_to_acquire' => 5000,
                'operation_cost' => 10,
                'carbon_footprint' => 1.2,
                'quality_factor' => 0.9,
                'min_speed' => 5,
                'avg_speed' => 10,
                'max_speed' => 15,
                'reliability_decay_days' => 60,
                'maintenance_interval_days' => 30,
                'min_maintenance_cost' => 50,
                'avg_maintenance_cost' => 100,
                'max_maintenance_cost' => 200,
                'min_maintenance_time_days' => 1,
                'avg_maintenance_time_days' => 2,
                'max_maintenance_time_days' => 4,
                'employee_profile_id' => optional($operator)->id,
            ],
        ];

        foreach ($machines as $data) {
            Machine::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}


