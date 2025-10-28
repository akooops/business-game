<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Company;
use App\Models\CompanyMachine;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\CalculationsService;
use App\Services\NotificationService;

class StartWorkAccedent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:start-work-accident';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start work accident';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing start work accident job...');

        $companies = Company::all();

        $machines = [
            'Standard Mixer -2-',
            'Emulsifier -2-',
            'High-precision mixer with filtration -2-',
        ];

        foreach($companies as $company){
            $companyMachine = $company->companyMachines()->whereHas('machine', function($query) use ($machines){
                $query->whereIn('name', $machines);
            })->where('status', CompanyMachine::STATUS_ACTIVE)->first();

            if(!$companyMachine){
                continue;
            }

            // Machine breaks
            $companyMachine->update([
                'status' => CompanyMachine::STATUS_BROKEN,
                'broken_at' => SettingsService::getCurrentTimestamp(),
                'current_reliability' => 0.2
            ]);

            // Cancel all production orders for the company machine
            $productionOrders = ProductionOrder::where([
                'company_machine_id' => $companyMachine->id,
                'status' => ProductionOrder::STATUS_IN_PROGRESS,
            ])->get();

            // Cancel each production order
            foreach($productionOrders as $productionOrder){ 
                $productionOrder->update([
                    'status' => ProductionOrder::STATUS_CANCELLED,
                ]);
            }

            // Calculate value loss
            $valueLoss = $companyMachine->current_value * $companyMachine->loss_on_sale_days;
            $companyMachine->current_value -= $valueLoss;

            $companyMachine->update([
                'current_value' => $companyMachine->current_value,
            ]);

            $affectedEmployee = Employee::find($companyMachine->employee_id);   

            if(!$affectedEmployee){
                continue;
            }

            $affectedEmployee->update([
                'efficiency_factor' => $affectedEmployee->efficiency_factor - $affectedEmployee->efficiency_factor * 0.2,
                'status' => Employee::STATUS_RESIGNED,
            ]);

            $employees = Employee::where('status', Employee::STATUS_ACTIVE)->where('company_id', $company->id)->get();

            foreach($employees as $employee){
                $employee->update([
                    'efficiency_factor' => $employee->efficiency_factor - $employee->efficiency_factor * 0.1,
                ]);
            }

            NotificationService::createWorkAccedentStartedNotification($companyMachine->company, $companyMachine->machine, $affectedEmployee);
        }
    }
} 