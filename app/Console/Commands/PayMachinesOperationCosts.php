<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\InventoryService;
use App\Services\ProductionService;

class PayMachinesOperationCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:pay-machines-operation-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pay machines operation costs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Paying machines operation costs...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            ProductionService::payMachineOperationCost($company);
        }
        
        $this->info('Machines operation costs paid successfully!');
    }
} 