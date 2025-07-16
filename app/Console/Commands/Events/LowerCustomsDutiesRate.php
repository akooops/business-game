<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;

class LowerCustomsDutiesRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:lower-customs-duties-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lower customs duties rate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing customs duties rate lowering job...');

        // Define the list of countries to block
        $countries = [
            'Algeria',
            'Morocco',
            'Tunisia',
        ];

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));
        $this->info('Countries to lower customs duties rate: ' . implode(', ', $countries));

        $rate = 0.1;

        foreach($countries as $country){
            $country = Country::where('name', $country)->first();

            if($country){
                $country->update([
                    'customs_duties_rate' => $country->customs_duties_rate * (1 - $rate),
                ]);

                $this->info('Country ' . $country->name . ' lowered successfully');
            }
        }

        NotificationService::createCountriesCustomsDutiesRateLoweredNotification($countries, $rate);
    }
} 