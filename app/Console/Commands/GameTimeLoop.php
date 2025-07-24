<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

        // Process technologies research
        $this->call('game:technolgies-research-processing');

        // Process purchases
        $this->call('game:purchases-processing');

        // Process sales
        $this->call('game:sales-processing');

        // Process sales
        $this->call('game:generate-new-sales');

        // Expire old job applications
        $this->call('game:expire-old-job-applications');

        // Process employees mood
        $this->call('game:process-employees-mood');

        // Pay machines operation costs
        $this->call('game:pay-machines-operation-costs');

        // Process production orders
        $this->call('game:production-orders-processing');

        // Process machines reliability
        $this->call('game:process-machines-reliability');

        // Every start of the month
        if($newTime->day == 1 ){
            // Pay employees salaries
            $this->call('game:pay-employees-salaries');
        }

        // Every week
        if($newTime->day % 7 == 0){
            // Change supplier prices and costs and shipping times
            $this->call('game:change-supplier-prices-and-costs');

            // Change wilayas costs and shipping times
            $this->call('game:change-wilayas-costs');

            // Pay inventory costs
            $this->call('game:pay-inventory-costs');
        }

        $this->info("New game time: " . $newTime->format('Y-m-d H:i:s'));
        $this->info('Game time loop completed successfully!');
    }
} 