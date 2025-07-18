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

            // Step 1: Process initiated sales - check time limits and cancel if needed
            $initiatedSales = $company->sales()->where('status', Sale::STATUS_INITIATED)->get();

            foreach($initiatedSales as $sale){
                $this->info('Processing initiated sale: ' . $sale->product->name);
                
                $currentTimestamp = SettingsService::getCurrentTimestamp();
                $saleInitiatedAt = $sale->initiated_at;
                $timeLimitDays = $sale->timelimit_days;
                
                // Check if sale has exceeded its time limit
                if($saleInitiatedAt->addDays($timeLimitDays) <= $currentTimestamp){
                    $sale->update(['status' => Sale::STATUS_CANCELLED]);
                    $this->info('Sale cancelled due to time limit: ' . $sale->product->name);
                    
                    // Send cancellation notification
                    NotificationService::createSaleCancelledNotification($sale);
                }
            }

            // Step 2: Process confirmed sales - check for delayed deliveries and send notifications
            $confirmedSales = $company->sales()->where('status', Sale::STATUS_CONFIRMED)->get();

            foreach($confirmedSales as $sale){
                $this->info('Processing confirmed sale: ' . $sale->product->name);

                $currentTimestamp = SettingsService::getCurrentTimestamp();
                
                // Check for delayed deliveries
                if($sale->estimated_delivered_at && $sale->estimated_delivered_at <= $currentTimestamp && 
                   $sale->real_delivered_at && $sale->real_delivered_at > $currentTimestamp) {
                    // Create delayed delivery notification
                    NotificationService::createSaleDeliveryDelayedNotification($sale);
                    $this->info('Delayed delivery notification created for: ' . $sale->product->name);
                }
            }

            // Step 3: Process actual deliveries
            $confirmedSales = $company->sales()->where('status', Sale::STATUS_CONFIRMED)->get();

            foreach($confirmedSales as $sale){
                $currentTimestamp = SettingsService::getCurrentTimestamp();
                
                // Process actual deliveries
                if($sale->real_delivered_at && $sale->real_delivered_at <= $currentTimestamp){
                    SalesService::deliveredSale($sale);
                    $this->info('Sale delivered: ' . $sale->product->name);
                }
            }
        }
        
        $this->info('Sales processing completed successfully!');
    }
} 