<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\AdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAdPackagesCompletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::find($this->companyId);
        
        if (!$company) {
            Log::warning("Company {$this->companyId} not found for ad packages completion processing");
            return;
        }

        Log::info("Processing ad packages completion for company: {$company->name}");
        
        AdsService::ProcessAdPackagesCompletion($company);
    }
}

