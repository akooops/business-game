<?php

namespace App\Console\Commands\Events;

use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\Company;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\NotificationService;

class DamageInventoryProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:damage-inventory-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Damage inventory product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Queueing damage inventory product job...');

        $companies = Company::all();
        $damageRate = 0.1;

        foreach($companies as $company){
            $companyProducts = $company->companyProducts()->where('available_stock', '>', 0)->get();

            foreach($companyProducts as $companyProduct){
                $product = $companyProduct->product;
                $quantity = $companyProduct->available_stock;

                if($product){
                    $damagedQuantity = $quantity * $damageRate;

                    $companyProduct->update(['available_stock' => $quantity - $damagedQuantity]);
                }

                InventoryMovement::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'movement_type' => InventoryMovement::MOVEMENT_TYPE_DAMAGED,
                    'quantity' => $damagedQuantity,
                    'moved_at' => SettingsService::getCurrentTimestamp(),
                    'note' => 'Inventory damaged with ' . $damageRate . '%',
                ]);
            }
        }

        NotificationService::createInventoryDamagedNotification($company, $damageRate);
    }
} 