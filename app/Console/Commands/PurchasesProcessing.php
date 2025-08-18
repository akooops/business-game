<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\ProcurementService;

class PurchasesProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:purchases-processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing purchases...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            ProcurementService::processDeliveredPurchase($company);
        }
        
        $this->info('Purchases processing completed successfully!');
    }
} 