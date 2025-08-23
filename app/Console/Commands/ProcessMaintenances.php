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

            MaintenanceService::completeMaintenance($company);
        }
        
        $this->info('Maintenances processing completed successfully!');
    }
} 