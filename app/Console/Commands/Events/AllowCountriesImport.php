<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;

class AllowCountriesImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:allow-countries-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow countries import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing countries import allowing job...');

        // Define the list of countries to block
        $countries = ['China', 'Morocco', 'Tunisia'];

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));
        $this->info('Countries to allow: ' . implode(', ', $countries));

        foreach($countries as $country){
            $country = Country::where('name', $country)->first();

            if($country){
                $country->update([
                    'allows_imports' => true,
                ]);

                $this->info('Country ' . $country->name . ' allowed successfully');
            }
        }

        NotificationService::createCountriesImportAllowedNotification($countries);
    }
} 