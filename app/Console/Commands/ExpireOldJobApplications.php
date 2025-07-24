<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\HrService;

class ExpireOldJobApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:expire-old-job-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire old job applications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Expiring old job applications...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            HrService::processAppliedEmployees($company);
        }
        
        $this->info('Expiring old job applications completed successfully!');
    }
} 