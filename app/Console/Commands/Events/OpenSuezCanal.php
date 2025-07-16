<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;
use App\Models\Supplier;
use App\Services\CalculationsService;

class OpenSuezCanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:open-suez-canal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open Suez canal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing Suez canal opening job...');

        // Define the list of countries that will be affected by the Suez canal opening
        $countries = ['Egypt', 'China'];
        $rate = 1.5;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));
        $this->info('Countries affected by Suez canal opening: ' . implode(', ', $countries));

        foreach($countries as $country){
            $country = Country::where('name', $country)->first();

            $supplier = Supplier::where('country_id', $country->id)->first();

            if($supplier){
                $supplier->update([
                    'min_shipping_cost' => $supplier->min_shipping_cost / $rate,
                    'max_shipping_cost' => $supplier->max_shipping_cost / $rate,
                    'avg_shipping_cost' => $supplier->avg_shipping_cost / $rate,
                    'real_shipping_cost' => CalculationsService::calculatePertValue($supplier->min_shipping_cost, $supplier->avg_shipping_cost, $supplier->max_shipping_cost),
                    'min_shipping_time_days' => $supplier->min_shipping_time_days / $rate,
                    'max_shipping_time_days' => $supplier->max_shipping_time_days / $rate,
                    'avg_shipping_time_days' => $supplier->avg_shipping_time_days / $rate,
                ]);

                $this->info('Country ' . $country->name . ' affected successfully');
            }
        }

        NotificationService::createSuezCanalOpenedNotification($countries, $rate);
    }
} 