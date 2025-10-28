<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Company;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\CalculationsService;
use App\Services\NotificationService;

class StartWorkersProtest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:start-workers-protest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start workers protest';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing start workers protest job...');

        $companies = Company::all();
        $rate = 0.15;

        $employeeProfiles = [
            'Worker',
        ];

        $employees = Employee::whereHas('employeeProfile', function($query) use ($employeeProfiles){
            $query->whereIn('name', $employeeProfiles);
        })->where('status', Employee::STATUS_ACTIVE)->get();   

        foreach($employees as $employee){
            $employee->update([
                'efficiency_factor' => $employee->efficiency_factor - $employee->efficiency_factor * $rate,
            ]);
        }

        NotificationService::createWorkersProtestStartedNotification($rate);
    }
} 