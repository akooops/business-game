<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;

class TechnolgiesResearchProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:technolgies-research-processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process technologies research';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing technologies research...');

        $companies = Company::all();

        foreach($companies as $company){
            $this->info('Processing company: ' . $company->name);

            TechnolgiesResearchService::processCompletedResearch($company);
        }
        
        $this->info('Technologies research processing completed successfully!');
    }
} 