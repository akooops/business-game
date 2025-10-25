<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Country;
use App\Services\NotificationService;
use App\Models\Supplier;
use App\Services\CalculationsService;
use App\Models\Product;
use App\Models\ProductDemand;

class StopHealthComplaint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:stop-health-complaint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop health complaint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing stop health complaint job...');

        // Define the list of countries that will be affected by the Suez canal opening
        $rate = 0.2;

        // Get the target timestamp from settings
        $currentTimestamp = SettingsService::getCurrentTimestamp();

        $this->info('Current timestamp: ' . $currentTimestamp->format('Y-m-d H:i:s'));

        $products = Product::get();

        foreach($products as $product){
            $productDemand = ProductDemand::where('product_id', $product->id)->get();

            foreach($productDemand as $demand){
                $demand->update([
                    'min_demand' => $demand->min_demand / (1 - $rate),
                    'max_demand' => $demand->max_demand / (1 - $rate),
                ]);
            }
        }

        NotificationService::createHealthComplaintEndedNotification();
    }
} 