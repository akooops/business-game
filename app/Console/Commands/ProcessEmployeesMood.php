<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\HrService;

class ProcessEmployeesMood extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-employees-mood';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process employees mood';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing employees mood...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            HrService::processEmployeesMood($company);
        }
        
        $this->info('Employees mood processed successfully!');
    }
} 