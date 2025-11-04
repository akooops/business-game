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
                'key' => 'game_start_timestamp',
                'name' => 'Game Start Timestamp',
                'type' => 'timestamp',
                'value' => '2025-01-01 08:00:00',
                'description' => 'The start timestamp of the game',
                'options' => null,
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
                'key' => 'game_speed',
                'name' => 'Game Speed',
                'type' => 'select',
                'value' => '1x',
                'description' => 'The speed of the game',
                'options' => ['0.25x', '0.5x', '1x', '2x', '4x'],
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
                'key' => 'ability_to_sell_machines',
                'name' => 'Ability to Sell Machines',
                'type' => 'select',
                'value' => 'yes',
                'description' => 'Whether the company can sell machines',
                'options' => ['yes', 'no'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_loss_on_sale_days_percentage',
                'name' => 'Min Loss on Sale Days Percentage',
                'type' => 'number',
                'value' => 0.35,
                'description' => 'The minimum loss on sale days percentage',
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

