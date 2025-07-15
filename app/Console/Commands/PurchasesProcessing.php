<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use App\Models\Purchase;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
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

            $companyPurchases = $company->purchases()->where('status', Purchase::STATUS_ORDERED)->get();

            foreach($companyPurchases as $companyPurchase){
                $this->info('Processing purchase: ' . $companyPurchase->product->name);

                if($companyPurchase->real_delivered_at <= SettingsService::getCurrentTimestamp()){
                    ProcurementService::deliveredPurchase($companyPurchase);
                    $this->info('Purchase delivered: ' . $companyPurchase->product->name);
                }
            }
        }
        
        $this->info('Purchases processing completed successfully!');
    }
} 