<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\InventoryService;

class ProcessExpiredInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-expired-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing expired inventory...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            InventoryService::expireInventory($company);
        }
        
        $this->info('Expired inventory processing completed successfully!');
    }
} 