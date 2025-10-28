<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\ProductionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMachinesValue implements ShouldQueue
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
            Log::warning("Company {$this->companyId} not found for machines value processing");
            return;
        }

        Log::info("Processing machines value for company: {$company->name}");
        
        ProductionService::calculateMachinesValue($company);
    }
}

