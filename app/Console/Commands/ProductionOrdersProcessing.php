<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Models\ProductionOrder;
use App\Services\ProductionService;

class ProductionOrdersProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:production-orders-processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process production orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing production orders...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            ProductionService::completeProduction($company);
        }

        $this->info('Production orders processing completed successfully!');
    }
} 