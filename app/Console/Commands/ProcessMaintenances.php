<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Maintenance;
use App\Services\MaintenanceService;

class ProcessMaintenances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-maintenances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process maintenances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing maintenances...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            $companyMachines = $company->companyMachines;

            foreach($companyMachines as $companyMachine){
                $companyMaintenances = Maintenance::where([
                    'company_machine_id' => $companyMachine->id, 
                    'status' => Maintenance::STATUS_IN_PROGRESS
                ])->get();

                foreach($companyMaintenances as $companyMaintenance){
                    $currentTimestamp = SettingsService::getCurrentTimestamp();
                    
                    // Process actual deliveries
                    if($companyMaintenance->completed_at && $companyMaintenance->completed_at <= $currentTimestamp){
                        MaintenanceService::completeMaintenance($companyMaintenance);
                        $this->info('Maintenance completed: ' . $companyMachine->machine->name);
                    }
                }
            }
        }
        
        $this->info('Maintenances processing completed successfully!');
    }
} 