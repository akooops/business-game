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

class ResetCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:reset-company {company_id=18 : The company ID to reset} {--force : Force reset without confirmation} {--keep-user : Keep the user account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a specific company by deleting all its related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        $company = Company::find($companyId);
        
        if (!$company) {
            $this->error("❌ Company with ID {$companyId} not found!");
            return 1;
        }

        $this->info("Found company: {$company->name} (ID: {$company->id})");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  This will DELETE ALL data for company '{$company->name}' (ID: {$companyId}). Are you sure?")) {
                $this->info('Company reset cancelled.');
                return;
            }
        }

        $this->info('Starting company reset...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Delete all company-related data
            $this->info('🗑️  Deleting company-related data...');
            
            $this->deleteWithCountForCompany(Ad::class, 'Ads', $companyId);
            $this->deleteWithCountForCompany(Notification::class, 'Notifications', $companyId);
            $this->deleteWithCountForCompany(Transaction::class, 'Transactions', $companyId);
            $this->deleteWithCountForCompany(Sale::class, 'Sales', $companyId);
            $this->deleteWithCountForCompany(Purchase::class, 'Purchases', $companyId);
            $this->deleteWithCountForCompany(ProductionOrder::class, 'Production Orders', $companyId);
            $this->deleteWithCountForCompany(Maintenance::class, 'Maintenances', $companyId);
            $this->deleteWithCountForCompany(Loan::class, 'Loans', $companyId);
            $this->deleteWithCountForCompany(InventoryMovement::class, 'Inventory Movements', $companyId);
            $this->deleteWithCountForCompany(Employee::class, 'Employees', $companyId);
            $this->deleteWithCountForCompany(CompanyTechnology::class, 'Company Technologies', $companyId);
            $this->deleteWithCountForCompany(CompanyProduct::class, 'Company Products', $companyId);
            $this->deleteWithCountForCompany(CompanyMachine::class, 'Company Machines', $companyId);

            $this->newLine();

            // Get user ID before deleting company
            $userId = $company->user_id;

            // Delete the company
            $this->info("🗑️  Deleting company '{$company->name}'...");
            $company->delete();
            $this->line("   Deleted company");

            // Optionally delete the user
            if (!$this->option('keep-user') && $userId) {
                $this->info('🗑️  Deleting associated user...');
                $user = User::find($userId);
                if ($user) {
                    $this->line("   Deleting user: {$user->email}");
                    $user->delete();
                    $this->line("   Deleted user");
                }
            } else if ($userId) {
                $this->line("   Kept user account (ID: {$userId})");
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Company reset completed successfully!');
            $this->info("📊 All data for company ID {$companyId} has been deleted");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Company reset failed: ' . $e->getMessage());
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
}

