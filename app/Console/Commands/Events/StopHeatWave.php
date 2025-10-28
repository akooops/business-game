<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\CalculationsService;
use App\Services\NotificationService;

class StopHeatWave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:stop-heat-wave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop heat wave';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing stop heat wave job...');

        $rate = 0.08;
        
        $products = [
            "Moisturizing cream (120 mL)",
            "Serum (30 mL)",
            "Shampoo (250 mL)",
            "Conditioner (250 mL)",
            "Deodorant (200 mL)",
            "Liquid Foundation (30 mL)",
            "BB Cream (30 mL)",
            "Lip Balm (10 mL)",
            "Mascara (100g)",
            "Women’s Perfume (100 mL)",
            "Men’s Perfume (100 mL)"
        ];

        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->get();   

        foreach($employees as $employee){
            $employee->update([
                'efficiency_factor' => $employee->efficiency_factor / (1 + $rate),
            ]);
        }

        $localSuppliers = Supplier::where('is_international', false)->get();

        $supplierProducts = SupplierProduct::whereHas('supplier', function($query) use ($localSuppliers){
            $query->whereIn('id', $localSuppliers->pluck('id'));
        })->get();

        foreach($supplierProducts as $supplierProduct){
            $supplierProduct->update([
                'min_sale_price' => $supplierProduct->min_sale_price / (1 + $rate),
                'max_sale_price' => $supplierProduct->max_sale_price / (1 + $rate),
                'real_sale_price' => CalculationsService::calcaulteRandomBetweenMinMax($supplierProduct->min_sale_price, $supplierProduct->max_sale_price),
            ]);
        }

        NotificationService::createHeatWaveEndedNotification($products, $rate);
    }
} 