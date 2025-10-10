<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Wilaya;
use App\Services\NotificationService;
use App\Services\CalculationsService;

class OpenOrmuzCanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:open-ormuz-canal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open Ormuz canal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing Ormuz canal opening job...');

        // Define the list of countries to block
        $rate = 0.15;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));

        $products = ["Silicones", "Surfactants", "Preservatives", "Thickener / Gelling Agent", "Packaging Material"];
        $countries = ["United Arab Emirates"];

        $suppliers = Supplier::whereHas('country', function($query) use ($countries){
            $query->whereIn('name', $countries);
        })->get();

        foreach($suppliers as $supplier){
            if($supplier){
                $minShippingCost = $supplier->min_shipping_cost / (1 + $rate);
                $maxShippingCost = $supplier->max_shipping_cost / (1 + $rate);
                $minShippingTimeDays = $supplier->min_shipping_time_days / (1 + $rate);
                $maxShippingTimeDays = $supplier->max_shipping_time_days / (1 + $rate);

                $supplier->update([
                    'min_shipping_cost' => $minShippingCost,
                    'max_shipping_cost' => $maxShippingCost,
                    'real_shipping_cost' => CalculationsService::calcaulteRandomBetweenMinMax($minShippingCost, $maxShippingCost),
                    'min_shipping_time_days' => $minShippingTimeDays,
                    'max_shipping_time_days' => $maxShippingTimeDays,
                    'real_shipping_time_days' => CalculationsService::calcaulteRandomBetweenMinMax($minShippingTimeDays, $maxShippingTimeDays),
                ]);

                $this->info('Supplier ' . $supplier->name . ' raised successfully with min shipping cost ' . $minShippingCost . ' and max shipping cost ' . $maxShippingCost);
            }
        }

        $supplierProducts = SupplierProduct::whereHas('product', function($query) use ($products){
            $query->whereIn('name', $products);
        })->get();

        foreach($supplierProducts as $supplierProduct){
            $supplierProduct->update([
                'min_sale_price' => $supplierProduct->min_sale_price / (1 + $rate),
                'max_sale_price' => $supplierProduct->max_sale_price / (1 + $rate),
                'real_sale_price' => CalculationsService::calcaulteRandomBetweenMinMax($supplierProduct->min_sale_price, $supplierProduct->max_sale_price),
            ]);
        }

        NotificationService::createOrmuzCanalOpenedNotification($products, $countries);
    }
} 