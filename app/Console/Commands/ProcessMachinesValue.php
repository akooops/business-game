<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\ProductionService;

class ProcessMachinesValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-machines-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process machines value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing machines value...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            ProductionService::calculateMachinesValue($company);
        }
        
        $this->info('Machines value processed successfully!');
    }
} 