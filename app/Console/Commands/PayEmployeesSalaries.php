<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\HrService;

class PayEmployeesSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:pay-employees-salaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pay employees salaries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Paying employees salaries...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            HrService::paySalaries($company);
        }
        
        $this->info('Employees salaries paid successfully!');
    }
} 