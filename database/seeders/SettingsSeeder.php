<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'game_status',
                'name' => 'Game Status',
                'type' => 'select',
                'value' => 'running',
                'description' => 'The status of the game',
                'options' => ['running', 'stopped'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'current_timestamp',
                'name' => 'Current Timestamp',
                'type' => 'timestamp',
                'value' => '2025-01-01 08:00:00',
                'description' => 'The current timestamp of the game',
                'options' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'demand_visiblity_ahead_weeks',
                'name' => 'Demand Visibility Ahead Weeks',
                'type' => 'number',
                'value' => 1,
                'description' => 'The number of weeks ahead that the demand will be visible to the players',
                'options' => [
                    'min' => 0,
                    'step' => 1,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'demand_visiblity_ahead_weeks_after_research',
                'name' => 'Demand Visibility Ahead Weeks After Research',
                'type' => 'number',
                'value' => 1,
                'description' => 'The number of weeks ahead that the demand will be visible to the players after the research',
                'options' => [
                    'min' => 0,
                    'step' => 1,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'demand_research_cost',
                'name' => 'Demand Research Cost',
                'type' => 'number',
                'value' => 100000,
                'description' => 'The cost of making a demand visible to the players',
                'options' => [
                    'min' => 0,
                    'step' => 0.001,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'demand_research_time_days',
                'name' => 'Demand Research Time Days',
                'type' => 'number',
                'value' => 1,
                'description' => 'The number of days that the demand research will take',
                'options' => [
                    'min' => 0,
                    'step' => 1,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'name' => $setting['name'],
                    'type' => $setting['type'],
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                    'options' => $setting['options'],
                    'created_at' => $setting['created_at'],
                    'updated_at' => $setting['updated_at'],
                ]
            );
        }
    }
}

