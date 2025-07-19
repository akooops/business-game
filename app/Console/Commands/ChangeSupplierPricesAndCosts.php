<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\CalculationsService;

class ChangeSupplierPricesAndCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:change-supplier-prices-and-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change supplier prices and costs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Changing supplier prices and costs...');

        $suppliers = Supplier::all();

        foreach($suppliers as $supplier){
            $this->info('Processing supplier: ' . $supplier->name);

            $supplier->update([
                'real_shipping_cost' => CalculationsService::calculatePertValue($supplier->min_shipping_cost, $supplier->avg_shipping_cost, $supplier->max_shipping_cost),
            ]);

            $supplierProducts = $supplier->supplierProducts()->get();

            foreach($supplierProducts as $supplierProduct){
                $this->info('Processing supplier product: ' . $supplierProduct->product->name);

                $supplierProduct->update([
                    'real_sale_price' => CalculationsService::calculatePertValue($supplierProduct->min_sale_price, $supplierProduct->avg_sale_price, $supplierProduct->max_sale_price),
                ]);
            }
        }
        
        $this->info('Technologies research processing completed successfully!');
    }
} 