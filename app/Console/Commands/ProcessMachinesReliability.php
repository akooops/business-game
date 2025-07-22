<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\MaintenanceService;

class ProcessMachinesReliability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-machines-reliability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process machines reliability';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing machines reliability...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            MaintenanceService::processMachinesReliability($company);
        }
        
        $this->info('Machines reliability processed successfully!');
    }
} 