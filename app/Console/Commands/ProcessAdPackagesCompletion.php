<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\AdsService;

class ProcessAdPackagesCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:process-ad-packages-completion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process ad packages completion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing ad packages completion...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);
            AdsService::ProcessAdPackagesCompletion($company);
        }
        
        $this->info('Ad packages completion processing completed successfully!');
    }
} 