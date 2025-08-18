<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\InventoryService;

class PayInventoryCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:pay-inventory-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pay inventory costs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Paying inventory costs...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            InventoryService::payInventoryCosts($company);
        }
        
        $this->info('Inventory costs paid successfully!');
    }
} 