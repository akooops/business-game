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
                $supplier->update([
                    'min_shipping_cost' => $supplier->min_shipping_cost * (1 + $rate),
                    'max_shipping_cost' => $supplier->max_shipping_cost * (1 + $rate),
                    'avg_shipping_cost' => $supplier->avg_shipping_cost * (1 + $rate),
                    'real_shipping_cost' => CalculationsService::calculatePertValue($supplier->min_shipping_cost, $supplier->avg_shipping_cost, $supplier->max_shipping_cost),
                ]);

                $this->info('Supplier ' . $supplier->name . ' raised successfully');
            }
        }

        $wilayas = Wilaya::get();

        foreach($wilayas as $wilaya){
            $wilaya->update([
                'min_shipping_cost' => $wilaya->min_shipping_cost * (1 + $rate),
                'max_shipping_cost' => $wilaya->max_shipping_cost * (1 + $rate),
                'avg_shipping_cost' => $wilaya->avg_shipping_cost * (1 + $rate),
                'real_shipping_cost' => CalculationsService::calculatePertValue($wilaya->min_shipping_cost, $wilaya->avg_shipping_cost, $wilaya->max_shipping_cost),
            ]);
        }

        NotificationService::createOilPriceRaisedNotification($rate);
    }
} 