<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\Company;
use App\Models\CompanyMachine;
use App\Models\CompanyProduct;
use App\Models\CompanyTechnology;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\ProductionOrder;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the game by deleting all company-related data and resetting settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  This will DELETE ALL game data (companies, users, transactions, etc.). Are you sure?')) {
                $this->info('Game reset cancelled.');
                return;
            }
        }

        $this->info('Starting game reset...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Step 1: Delete all company-related data (respecting foreign keys)
            $this->info('🗑️  Deleting company-related data...');
            
            $this->deleteWithCount(Ad::class, 'Ads');
            $this->deleteWithCount(Notification::class, 'Notifications');
            $this->deleteWithCount(Transaction::class, 'Transactions');
            $this->deleteWithCount(Sale::class, 'Sales');
            $this->deleteWithCount(Purchase::class, 'Purchases');
            $this->deleteWithCount(ProductionOrder::class, 'Production Orders');
            $this->deleteWithCount(Maintenance::class, 'Maintenances');
            $this->deleteWithCount(Loan::class, 'Loans');
            $this->deleteWithCount(InventoryMovement::class, 'Inventory Movements');
            $this->deleteWithCount(Employee::class, 'Employees');
            $this->deleteWithCount(CompanyTechnology::class, 'Company Technologies');
            $this->deleteWithCount(CompanyProduct::class, 'Company Products');
            $this->deleteWithCount(CompanyMachine::class, 'Company Machines');

            $this->newLine();
            
            // Step 2: Get company user IDs before deleting companies
            $this->info('👥 Collecting company user IDs...');
            $companyUserIds = Company::pluck('user_id')->toArray();
            $this->line("   Found " . count($companyUserIds) . " company users");

            // Step 3: Delete companies
            $this->deleteWithCount(Company::class, 'Companies');

            // Step 4: Delete users that were associated with companies
            $this->info('🗑️  Deleting company users...');
            $deletedUsers = User::whereIn('id', $companyUserIds)->delete();
            $this->line("   Deleted {$deletedUsers} company users");

            $this->newLine();

            // Step 5: Reset settings based on SettingsSeeder
            $this->info('⚙️  Resetting game settings...');
            $this->resetSettings();

            DB::commit();

            $this->newLine();
            $this->info('✅ Game reset completed successfully!');
            $this->info('📊 All company data has been deleted');
            $this->info('⚙️  Settings have been reset to defaults');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Game reset failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    /**
     * Delete records and display count
     */
    private function deleteWithCount($model, $name)
    {
        $count = $model::count();
        $model::query()->delete();
        $this->line("   Deleted {$count} {$name}");
    }

    /**
     * Reset settings to default values from SettingsSeeder
     */
    private function resetSettings()
    {
        $settings = [
            [
                'key' => 'game_status',
                'name' => 'Game Status',
                'type' => 'select',
                'value' => 'running',
                'description' => 'The status of the game',
                'options' => ['running', 'stopped'],
            ],
            [
                'key' => 'game_start_timestamp',
                'name' => 'Game Start Timestamp',
                'type' => 'timestamp',
                'value' => '2025-01-01 08:00:00',
                'description' => 'The start timestamp of the game',
                'options' => null,
            ],
            [
                'key' => 'current_timestamp',
                'name' => 'Current Timestamp',
                'type' => 'timestamp',
                'value' => '2025-01-01 08:00:00',
                'description' => 'The current timestamp of the game',
                'options' => null,
            ],
            [
                'key' => 'game_speed',
                'name' => 'Game Speed',
                'type' => 'select',
                'value' => '1x',
                'description' => 'The speed of the game',
                'options' => ['0.25x', '0.5x', '1x', '2x', '4x'],
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
                ]
            );
            $this->line("   Reset setting: {$setting['name']}");
        }
    }
}

