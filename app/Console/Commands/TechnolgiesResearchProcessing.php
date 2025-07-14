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

            $companyTechnologies = $company->companyTechnologies()->where('completed_at', null)->get();

            foreach($companyTechnologies as $companyTechnology){
                $this->info('Processing technology research: ' . $companyTechnology->technology->name);

                if($companyTechnology->estimated_completed_at <= SettingsService::getCurrentTimestamp()){
                    TechnolgiesResearchService::completedResearch($companyTechnology);
                    $this->info('Technology research completed: ' . $companyTechnology->technology->name);
                }
            }
        }
        
        $this->info('Technologies research processing completed successfully!');
    }
} 