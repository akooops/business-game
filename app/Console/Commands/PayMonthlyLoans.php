<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\LoansService;

class PayMonthlyLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:pay-monthly-loans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pay monthly loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Paying monthly loans...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            LoansService::processMonthlyPayment($company);
        }
        
        $this->info('Monthly loans paid successfully!');
    }
} 