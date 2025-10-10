<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;

class BlockCountriesImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:block-countries-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Block countries import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing countries import blocking job...');

        // Define the list of countries to block
        $countries = ['Spain'];

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));
        $this->info('Countries to block: ' . implode(', ', $countries));

        foreach($countries as $country){
            $country = Country::where('name', $country)->first();

            if($country){
                $country->update([
                    'allows_imports' => false,
                ]);

                $this->info('Country ' . $country->name . ' blocked successfully');
            }
        }

        NotificationService::createCountriesImportBlockedNotification($countries);
    }
} 