<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Supplier;
use App\Models\Wilaya;
use App\Services\NotificationService;
use App\Services\CalculationsService;

class RaiseOilPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:raise-oil-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Raise oil price';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing oil price raising job...');

        // Define the list of countries to block
        $rate = 0.1;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));

        $suppliers = Supplier::get();

        foreach($suppliers as $supplier){
            if($supplier){
                $minShippingCost = $supplier->min_shipping_cost * (1 + $rate);
                $maxShippingCost = $supplier->max_shipping_cost * (1 + $rate);

                $supplier->update([
                    'min_shipping_cost' => $minShippingCost,
                    'max_shipping_cost' => $maxShippingCost,
                    'real_shipping_cost' => CalculationsService::calcaulteRandomBetweenMinMax($minShippingCost, $maxShippingCost),
                ]);

                $this->info('Supplier ' . $supplier->name . ' raised successfully with min shipping cost ' . $minShippingCost . ' and max shipping cost ' . $maxShippingCost);
            }
        }

        $wilayas = Wilaya::get();

        foreach($wilayas as $wilaya){
            $minShippingCost = $wilaya->min_shipping_cost * (1 + $rate);
            $maxShippingCost = $wilaya->max_shipping_cost * (1 + $rate);

            $wilaya->update([
                'min_shipping_cost' => $minShippingCost,
                'max_shipping_cost' => $maxShippingCost,
                'real_shipping_cost' => CalculationsService::calcaulteRandomBetweenMinMax($wilaya->min_shipping_cost, $wilaya->max_shipping_cost),
            ]);

            $this->info('Wilaya ' . $wilaya->name . ' raised successfully with min shipping cost ' . $minShippingCost . ' and max shipping cost ' . $maxShippingCost);
        }

        NotificationService::createOilPriceRaisedNotification($rate);
    }
} 