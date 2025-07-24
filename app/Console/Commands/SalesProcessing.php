<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\ProcurementService;
use App\Services\NotificationService;
use App\Services\SalesService;

class SalesProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:sales-processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing sales...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            // Cancel sales that have exceeded their time limit
            SalesService::cancelSales($company);
            
            // Process delivered sales
            SalesService::processDeliveredSale($company);
        }
        
        $this->info('Sales processing completed successfully!');
    }
} 