<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use Carbon\Carbon;

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

        // Increment by 1 hour
        $newTime = $currentTime->copy()->addHour();
        
        // Check if we need to handle business hours
        $newTime = $this->handleBusinessHours($newTime);
        
        // Update the game time
        SettingsService::setCurrentTimestamp($newTime);

        // Process technologies research
        $this->call('game:technolgies-research-processing');
        
        $this->info("New game time: " . $newTime->format('Y-m-d H:i:s'));
        $this->info('Game time loop completed successfully!');
    }

    /**
     * Handle business hours logic (08:00-16:00)
     */
    private function handleBusinessHours(Carbon $time): Carbon
    {
        $hour = (int) $time->format('H');
        
        // If time is between 16:00 and 08:00, skip to next business day
        if ($hour >= 16 || $hour < 8) {
            if ($hour >= 16) {
                // It's after 16:00, move to next day at 08:00
                $time = $time->copy()->addDay()->setTime(8, 0, 0);
                $this->info("Business day ended. Moving to next day at 08:00");
            } else {
                // It's before 08:00, move to 08:00 of current day
                $time = $time->copy()->setTime(8, 0, 0);
                $this->info("Before business hours. Moving to 08:00");
            }
        }
        
        return $time;
    }
} 