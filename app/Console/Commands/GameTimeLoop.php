<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessTechnologiesResearch;
use App\Jobs\ProcessPurchases;
use App\Jobs\ProcessSales;
use App\Jobs\ExpireOldJobApplications;
use App\Jobs\ProcessEmployeesMood;
use App\Jobs\ProcessProductionOrders;
use App\Jobs\ProcessMachinesReliability;
use App\Jobs\ProcessMachinesValue;
use App\Jobs\ProcessMaintenances;
use App\Jobs\ProcessAdPackagesCompletion;
use App\Jobs\PayEmployeesSalaries;
use App\Jobs\PayMonthlyLoans;
use App\Jobs\ChangeSupplierPricesAndCosts;
use App\Jobs\PayInventoryCosts;
use App\Jobs\PayMachinesOperationCosts;
use App\Jobs\GenerateNewSales;
use App\Models\Company;

class GameTimeLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:time-loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment game time by 1 hour and manage business days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(SettingsService::isStopped()){
            $this->info('Game is stopped. Exiting...');
            return;
        }

        $currentTime = SettingsService::getCurrentTimestamp();
        $this->info("Current game time: " . $currentTime->format('Y-m-d H:i:s'));

        // Increment by current game speed
        $newTime = $currentTime->copy()->addDays(SettingsService::getGameSpeed());
        
        // Update the game time
        SettingsService::setCurrentTimestamp($newTime);

        // Fetch company IDs once (lightweight query)
        $companyIds = Company::pluck('id')->toArray();
        $totalCompanies = count($companyIds);
        $this->info("Dispatching jobs for {$totalCompanies} companies...");

        // Dispatch per-company jobs asynchronously
        // This ensures parallel processing and better performance
        
        foreach($companyIds as $companyId) {
            // Process technologies research
            ProcessTechnologiesResearch::dispatch($companyId);

            // Process purchases
            ProcessPurchases::dispatch($companyId);

            // Process sales
            ProcessSales::dispatch($companyId);

            // Expire old job applications
            ExpireOldJobApplications::dispatch($companyId);

            // Process employees mood
            ProcessEmployeesMood::dispatch($companyId);

            // Process production orders
            ProcessProductionOrders::dispatch($companyId);

            // Process machines reliability
            ProcessMachinesReliability::dispatch($companyId);

            // Process machines value
            ProcessMachinesValue::dispatch($companyId);

            // Process maintenances
            ProcessMaintenances::dispatch($companyId);

            // Process ad packages completion
            ProcessAdPackagesCompletion::dispatch($companyId);
        }

        // Every start of the month
        if($newTime->day == 1 ){
            foreach($companyIds as $companyId) {
                // Pay employees salaries
                PayEmployeesSalaries::dispatch($companyId);

                // Pay monthly loans
                PayMonthlyLoans::dispatch($companyId);
            }

            // Change supplier prices and costs (system-wide, not per-company)
            ChangeSupplierPricesAndCosts::dispatch();
        }

        // Every week
        if($newTime->day % 7 == 0){
            foreach($companyIds as $companyId) {
                // Pay inventory costs
                PayInventoryCosts::dispatch($companyId);

                // Pay machines operation costs
                PayMachinesOperationCosts::dispatch($companyId);

                // Generate new sales
                GenerateNewSales::dispatch($companyId);
            }
        }

        $totalJobsDispatched = $totalCompanies * 10; // Base jobs
        if($newTime->day == 1) $totalJobsDispatched += $totalCompanies * 2; // Monthly
        if($newTime->day % 7 == 0) $totalJobsDispatched += $totalCompanies * 3 + 1; // Weekly + supplier

        $this->info("New game time: " . $newTime->format('Y-m-d H:i:s'));
        $this->info("Dispatched {$totalJobsDispatched} jobs for processing!");
        $this->info('Game time loop completed successfully!');
    }
} 