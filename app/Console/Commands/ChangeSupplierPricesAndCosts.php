<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use Illuminate\Console\Command;
use App\Services\ProcurementService;

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

            ProcurementService::changeSupplierPricesAndCosts($supplier);
        }
        
        $this->info('Supplier prices and costs changed successfully!');
    }
} 