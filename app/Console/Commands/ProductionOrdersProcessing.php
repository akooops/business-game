<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\ProductionOrder;
use App\Services\ProductionService;

class ProductionOrdersProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:production-orders-processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process production orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing production orders...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            $companyMachines = $company->companyMachines;

            foreach($companyMachines as $companyMachine){
                $companyProductionOrders = ProductionOrder::where([
                    'company_machine_id' => $companyMachine->id, 
                    'status' => ProductionOrder::STATUS_IN_PROGRESS
                ])->get();

                foreach($companyProductionOrders as $companyProductionOrder){
                    $currentTimestamp = SettingsService::getCurrentTimestamp();
                    
                    // Process actual deliveries
                    if($companyProductionOrder->real_completed_at && $companyProductionOrder->real_completed_at <= $currentTimestamp){
                        ProductionService::completeProduction($companyProductionOrder);
                        $this->info('Production order completed: ' . $companyProductionOrder->product->name);
                    }
                }
            }
        }
        
        $this->info('Purchases processing completed successfully!');
    }
} 