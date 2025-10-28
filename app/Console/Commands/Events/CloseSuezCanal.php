<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;
use App\Models\Supplier;
use App\Services\CalculationsService;

class CloseSuezCanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:close-suez-canal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close Suez canal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing Suez canal closing job...');

        // Define the list of countries that will be affected by the Suez canal closing
        $countries = ['China', 'India'];
        $rate = 0.2;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));
        $this->info('Countries affected by Suez canal closing: ' . implode(', ', $countries));

        foreach($countries as $country){
            $country = Country::where('name', $country)->first();

            $supplier = Supplier::where('country_id', $country->id)->first();

            if($supplier){
                $minShippingCost = $supplier->min_shipping_cost + $supplier->min_shipping_cost * $rate;
                $maxShippingCost = $supplier->max_shipping_cost + $supplier->max_shipping_cost * $rate;
                $minShippingTimeDays = $supplier->min_shipping_time_days + $supplier->min_shipping_time_days * $rate;
                $maxShippingTimeDays = $supplier->max_shipping_time_days + $supplier->max_shipping_time_days * $rate;

                $supplier->update([
                    'min_shipping_cost' => $minShippingCost,
                    'max_shipping_cost' => $maxShippingCost,
                    'min_shipping_time_days' => $minShippingTimeDays,
                    'max_shipping_time_days' => $maxShippingTimeDays,
                ]);

                $this->info('Country ' . $country->name . ' affected successfully');
            }
        }

        NotificationService::createSuezCanalClosedNotification($countries, $rate);
    }
} 