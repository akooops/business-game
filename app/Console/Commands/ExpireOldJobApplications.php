<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\ProcurementService;
use App\Services\NotificationService;
use App\Services\SalesService;

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

            // Step 1: Process applied employees - check time limits and expire if needed
            $appliedEmployees = $company->employees()->where('status', Employee::STATUS_APPLIED)->get();

            foreach($appliedEmployees as $employee){
                $this->info('Processing applied employee: ' . $employee->name);
                
                $currentTimestamp = SettingsService::getCurrentTimestamp();
                $appliedAt = $employee->applied_at;
                $timeLimitDays = $employee->timelimit_days;
                
                // Check if sale has exceeded its time limit
                if($appliedAt->addDays($timeLimitDays) <= $currentTimestamp){
                    $employee->delete();
                    $this->info('Employee expired: ' . $employee->name);
                }
            }
        }
        
        $this->info('Expiring old job applications completed successfully!');
    }
} 