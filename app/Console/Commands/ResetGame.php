<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\Company;
use App\Models\CompanyMachine;
use App\Models\CompanyProduct;
use App\Models\CompanyTechnology;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\ProductionOrder;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the game by clearing company-related data while keeping company and user records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  This will DELETE company-related data for ALL companies (companies and users will be kept). Are you sure?")) {
                $this->info('Game reset cancelled.');
                return 0;
            }
        }

        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->info('No companies found to reset.');
            return 0;
        }

        $this->info('Starting full game reset...');
        $this->newLine();

        DB::beginTransaction();

        try {
            foreach ($companies as $company) {
                $this->resetCompanyData($company);
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Game reset completed successfully!');
            $this->info('📊 All company-related data has been cleared while retaining company and user records.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Game reset failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    /**
     * Delete records for a specific company and display count
     */
    private function deleteWithCountForCompany($model, $name, $companyId)
    {
        $count = $model::where('company_id', $companyId)->count();
        $model::where('company_id', $companyId)->delete();
        $this->line("   Deleted {$count} {$name}");
    }

    /**
     * Reset the provided company's related data while keeping core records.
     */
    private function resetCompanyData(Company $company): void
    {
        $companyId = $company->id;

        $this->info("🔄 Resetting company: {$company->name} (ID: {$companyId})");

        $this->deleteWithCountForCompany(Ad::class, 'Ads', $companyId);

        $userId = $company->user_id;
        if ($userId) {
            $notificationCount = Notification::where('user_id', $userId)->count();
            Notification::where('user_id', $userId)->delete();
            $this->line("   Deleted {$notificationCount} Notifications");
        }

        $this->deleteWithCountForCompany(Transaction::class, 'Transactions', $companyId);
        $this->deleteWithCountForCompany(Sale::class, 'Sales', $companyId);
        $this->deleteWithCountForCompany(Purchase::class, 'Purchases', $companyId);

        $companyMachineIds = CompanyMachine::where('company_id', $companyId)->pluck('id')->toArray();
        if (count($companyMachineIds) > 0) {
            $productionOrderCount = ProductionOrder::whereIn('company_machine_id', $companyMachineIds)->count();
            ProductionOrder::whereIn('company_machine_id', $companyMachineIds)->delete();
            $this->line("   Deleted {$productionOrderCount} Production Orders");

            $maintenanceCount = Maintenance::whereIn('company_machine_id', $companyMachineIds)->count();
            Maintenance::whereIn('company_machine_id', $companyMachineIds)->delete();
            $this->line("   Deleted {$maintenanceCount} Maintenances");
        }

        $this->deleteWithCountForCompany(Loan::class, 'Loans', $companyId);
        $this->deleteWithCountForCompany(InventoryMovement::class, 'Inventory Movements', $companyId);
        $this->deleteWithCountForCompany(Employee::class, 'Employees', $companyId);
        $this->deleteWithCountForCompany(CompanyTechnology::class, 'Company Technologies', $companyId);
        $this->deleteWithCountForCompany(CompanyProduct::class, 'Company Products', $companyId);
        $this->deleteWithCountForCompany(CompanyMachine::class, 'Company Machines', $companyId);

        $this->line('   Company and user records retained.');
        $this->newLine();
    }
}

