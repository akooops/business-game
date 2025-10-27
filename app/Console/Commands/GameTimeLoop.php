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

        // Dispatch jobs to process game mechanics asynchronously
        // This ensures the time loop completes quickly and consistently
        
        // Process technologies research
        ProcessTechnologiesResearch::dispatch();

        // Process purchases
        ProcessPurchases::dispatch();

        // Process sales
        ProcessSales::dispatch();

        // Expire old job applications
        ExpireOldJobApplications::dispatch();

        // Process employees mood
        ProcessEmployeesMood::dispatch();

        // Process production orders
        ProcessProductionOrders::dispatch();

        // Process machines reliability
        ProcessMachinesReliability::dispatch();

        // Process machines value
        ProcessMachinesValue::dispatch();

        // Process maintenances
        ProcessMaintenances::dispatch();

        // Process ad packages completion
        ProcessAdPackagesCompletion::dispatch();

        // Every start of the month
        if($newTime->day == 1 ){
            // Pay employees salaries
            PayEmployeesSalaries::dispatch();

            // Pay monthly loans
            PayMonthlyLoans::dispatch();

            // Change supplier prices and costs and shipping times
            ChangeSupplierPricesAndCosts::dispatch();
        }

        // Every week
        if($newTime->day % 7 == 0){
            // Pay inventory costs
            PayInventoryCosts::dispatch();

            // Pay machines operation costs
            PayMachinesOperationCosts::dispatch();

            // Generate new sales
            GenerateNewSales::dispatch(); 
        }

        $this->info("New game time: " . $newTime->format('Y-m-d H:i:s'));
        $this->info('Game time loop completed successfully!');
    }
} 