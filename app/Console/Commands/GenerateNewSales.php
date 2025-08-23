<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SalesService;

class GenerateNewSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:generate-new-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new sales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing sales...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Generating demand for company: ' . $company->name);

            $demand = SalesService::generateDemand($company);

            $this->info('Demand generated for company: ' . $company->name . ' : ' . $demand);
        }
        
        $this->info('Purchases processing completed successfully!');
    }
} 