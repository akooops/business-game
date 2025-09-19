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
                'operations_cost' => 10,
                'carbon_footprint' => 1.2,
                'quality_factor' => 0.9,
                'min_speed' => 5,
                'max_speed' => 15,
                'reliability_decay_days' => 60,
                'min_maintenance_cost' => 50,
                'max_maintenance_cost' => 200,
                'min_maintenance_time_days' => 1,
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


