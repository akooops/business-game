<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\ProcurementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPurchases implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $companyId;

    /**
     * Create a new job instance.
     */
    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $company = Company::find($this->companyId);
        
        if (!$company) {
            Log::warning("Company {$this->companyId} not found for purchases processing");
            return;
        }

        Log::info("Processing purchases for company: {$company->name}");
        
        ProcurementService::processDeliveredPurchase($company);
    }
}

