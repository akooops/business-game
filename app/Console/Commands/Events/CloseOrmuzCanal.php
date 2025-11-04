<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Wilaya;
use App\Services\NotificationService;
use App\Services\CalculationsService;

class CloseOrmuzCanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:close-ormuz-canal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close Ormuz canal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing Ormuz canal closing job...');

        // Define the list of countries to block
        $rate = 0.2;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));

        $products = ["Silicones (L)", "Surfactants (L)", "Preservatives (Kg)", "Thickener / Gelling Agent (Kg)", "Packaging (unity)"];
        $countries = ["United Arab Emirates"];

        $suppliers = Supplier::whereHas('country', function($query) use ($countries){
            $query->whereIn('name', $countries);
        })->get();

        foreach($suppliers as $supplier){
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

                $this->info('Supplier ' . $supplier->name . ' raised successfully with min shipping cost ' . $minShippingCost . ' and max shipping cost ' . $maxShippingCost);
            }
        }

        $supplierProducts = SupplierProduct::whereHas('product', function($query) use ($products){
            $query->whereIn('name', $products);
        })->get();

        foreach($supplierProducts as $supplierProduct){
            $supplierProduct->update([
                'min_sale_price' => $supplierProduct->min_sale_price + $supplierProduct->min_sale_price * $rate,
                'max_sale_price' => $supplierProduct->max_sale_price + $supplierProduct->max_sale_price * $rate,
            ]);
        }

        NotificationService::createOrmuzCanalClosedNotification($products, $countries, $rate);
    }
} 