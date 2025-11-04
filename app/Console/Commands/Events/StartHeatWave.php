<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Company;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\CalculationsService;
use App\Services\NotificationService;

class StartHeatWave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:start-heat-wave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start heat wave';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing start heat wave job...');

        $companies = Company::all();
        $rate = 0.4;

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

        foreach($companies as $company){
            $companyProducts = $company->companyProducts()->whereHas('product', function($query) use ($products){
                $query->whereIn('name', $products);
            })->where('available_stock', '>', 0)->get();

            foreach($companyProducts as $companyProduct){
                $product = $companyProduct->product;
                $quantity = $companyProduct->available_stock;
                $overAllDamagedQuantity = 0;

                if($product){
                    $companyInventory = $company->inventoryMovements()->where([
                        'product_id' => $product->id,
                        'movement_type' => InventoryMovement::MOVEMENT_TYPE_IN,
                    ])->get();

                    foreach($companyInventory as $inventory){
                        $leftAvailableStock = $inventory->current_quantity;

                        if($leftAvailableStock <= 0){
                            continue;
                        }

                        $damagedQuantity = $leftAvailableStock * $rate;

                        $inventory->update([
                            'current_quantity' => $inventory->current_quantity - $damagedQuantity,
                        ]);

                        $companyProduct->update(['available_stock' => $companyProduct->available_stock - $damagedQuantity]);

                        $overAllDamagedQuantity += $damagedQuantity;
                    }
                }

                if($overAllDamagedQuantity > 0){
                    InventoryMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                        'movement_type' => InventoryMovement::MOVEMENT_TYPE_DAMAGED,
                        'original_quantity' => $overAllDamagedQuantity,
                        'current_quantity' => $overAllDamagedQuantity,
                        'moved_at' => SettingsService::getCurrentTimestamp(),
                        'note' => 'Inventory damaged with ' . $rate * 100 . '% by heat wave',
                    ]);
                }
            }
        }

        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->get();   

        foreach($employees as $employee){
            $employee->update([
                'efficiency_factor' => $employee->efficiency_factor - $employee->efficiency_factor * $rate,
            ]);
        }

        $localSuppliers = Supplier::where('is_international', false)->get();

        $supplierProducts = SupplierProduct::whereHas('supplier', function($query) use ($localSuppliers){
            $query->whereIn('id', $localSuppliers->pluck('id'));
        })->get();

        foreach($supplierProducts as $supplierProduct){
            $supplierProduct->update([
                'min_sale_price' => $supplierProduct->min_sale_price + $supplierProduct->min_sale_price * $rate,
                'max_sale_price' => $supplierProduct->max_sale_price + $supplierProduct->max_sale_price * $rate,
            ]);
        }

        NotificationService::createHeatWaveStartedNotification($products, $rate);
    }
} 