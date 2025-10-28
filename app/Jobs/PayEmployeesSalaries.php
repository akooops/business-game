<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\HrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PayEmployeesSalaries implements ShouldQueue
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
            Log::warning("Company {$this->companyId} not found for paying employees salaries");
            return;
        }

        Log::info("Paying employees salaries for company: {$company->name}");
        
        HrService::paySalaries($company);
    }
}

