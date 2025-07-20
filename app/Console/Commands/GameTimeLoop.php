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

        // Pay inventory costs
        $this->call('game:pay-inventory-costs');

        // Change supplier prices and costs
        $this->call('game:change-supplier-prices-and-costs');
        $this->call('game:change-wilayas-costs');

        // Process sales
        $this->call('game:generate-new-sales');

        // Process employees mood
        $this->call('game:process-employees-mood');

        if($newTime->day == 1 ){
            // Pay employees salaries
            $this->call('game:pay-employees-salaries');
        }

        $this->info("New game time: " . $newTime->format('Y-m-d H:i:s'));
        $this->info('Game time loop completed successfully!');
    }
} 