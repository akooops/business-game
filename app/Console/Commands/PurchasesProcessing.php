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
use App\Services\NotificationService;

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

                $currentTimestamp = SettingsService::getCurrentTimestamp();
                
                // Check for delayed deliveries
                if($companyPurchase->estimated_delivered_at <= $currentTimestamp && $companyPurchase->real_delivered_at > $currentTimestamp) {
                    // Create delayed delivery notification
                    NotificationService::createPurchaseDeliveryDelayedNotification($companyPurchase);
                    $this->info('Delayed delivery notification created for: ' . $companyPurchase->product->name);
                }
                
                // Process actual deliveries
                if($companyPurchase->real_delivered_at && $companyPurchase->real_delivered_at <= $currentTimestamp){
                    ProcurementService::deliveredPurchase($companyPurchase);
                    $this->info('Purchase delivered: ' . $companyPurchase->product->name);
                }
            }
        }
        
        $this->info('Purchases processing completed successfully!');
    }
} 