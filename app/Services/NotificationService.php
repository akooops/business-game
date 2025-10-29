<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Notification;

class NotificationService
{
    // ------------------------------------------------------------
    // Technologies
    // ------------------------------------------------------------
    public static function createTechnologyResearchStartedNotification($company, $technology, $companyTechnology)
    {
        return Notification::create([
            'type' => Notification::TYPE_TECHNOLOGY_RESEARCH_STARTED,
            'title' => 'Technology Research Started',
            'message' => "Technology ({$technology->name}) research started at {$companyTechnology->started_at->format('Y-m-d')}",
            'url' => route('company.technologies.index'),
            'user_id' => $company->user->id,
        ]);
    }

    public static function createTechnologyResearchCompletedNotification($company, $technology, $companyTechnology)
    {
        return Notification::create([
            'type' => Notification::TYPE_TECHNOLOGY_RESEARCH_COMPLETED,
            'title' => 'Technology Research Completed',
            'message' => "Technology ({$technology->name}) research completed at {$companyTechnology->completed_at->format('Y-m-d')}. You have unlocked {$technology->products->count()} products!",
            'url' => route('company.technologies.index'),
            'user_id' => $company->user->id,
        ]);
    }

    // ------------------------------------------------------------
    // Purchases
    // ------------------------------------------------------------
    public static function createPurchaseOrderedNotification($company, $purchase, $supplier, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_ORDERED,
            'title' => 'Purchase Ordered',
            'message' => "Purchase initiated for {$product->name} at " . $purchase->ordered_at->format('Y-m-d') . " from {$supplier->name} with quantity of {$quantity}.",
            'url' => route('company.purchases.index'),
            'user_id' => $company->user->id,
        ]);
    }

    public static function createPurchaseDeliveredNotification($company, $purchase, $supplier, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_DELIVERED,
            'title' => 'Purchase Delivered',
            'message' => "Purchase delivered for {$product->name} at " . $purchase->delivered_at->format('Y-m-d') . " from {$supplier->name} with quantity of {$quantity}.",
            'url' => route('company.purchases.index'),
            'user_id' => $company->user->id,
        ]);
    }

    public static function createPurchaseCancelledNotification($company, $purchase, $supplier, $product, $quantity, $cancelledReason){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_CANCELLED,
            'title' => 'Purchase Cancelled',
            'message' => "Purchase cancelled for {$product->name} at " . $purchase->cancelled_at->format('Y-m-d') . " from {$supplier->name} with quantity of {$quantity}. Reason: " . $cancelledReason . ".",
            'url' => route('company.purchases.index'),
            'user_id' => $company->user->id,
        ]);
    }

    // ------------------------------------------------------------
    // Inventory
    // ------------------------------------------------------------
    public static function createInventoryExpiredNotification($company, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_EXPIRED,
            'title' => 'Inventory Expired',
            'message' => "Inventory expired for {$product->name} with quantity of {$quantity}.",
            'url' => route('company.inventory.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createInventoryCostsPaidNotification($company, $product, $quantity, $totalCost){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_COSTS_PAID,
            'title' => 'Inventory Costs Paid',
            'message' => "Inventory costs paid for {$product->name} for the last week for {$quantity} units. Total cost: DZD {$totalCost}.",
            'url' => route('company.inventory.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Sales
    // ------------------------------------------------------------
    public static function createSaleInitiatedNotification($company, $product, $numberOfSales){
        return Notification::create([
            'type' => Notification::TYPE_SALE_INITIATED,
            'title' => 'Sale Initiated',
            'message' => "{$numberOfSales} new sales for {$product->name}.",
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createSaleDeliveredNotification($company, $sale, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_SALE_DELIVERED,
            'title' => 'Sale Delivered',
            'message' => "Sale delivered for {$product->name} with quantity of {$quantity}.",
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createSaleCancelledNotification($company, $sale, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_SALE_CANCELLED,
            'title' => 'Sale Cancelled',
            'message' => "Sale cancelled for {$product->name} with quantity of {$quantity} because it exceeded the time limit.",
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // Batch notification for multiple sales initiated
    public static function createBatchSalesInitiatedNotification($company, $salesData){
        $totalSales = count($salesData);
        
        // Build detailed message
        $message = "New demand for {$totalSales} product(s).";
        
        return Notification::create([
            'type' => Notification::TYPE_SALE_INITIATED,
            'title' => 'New Sales Generated',
            'message' => rtrim($message),
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // Batch notification for multiple sales cancelled
    public static function createBatchSalesCancelledNotification($company, $cancelledData){
        $totalCancelled = count($cancelledData);
        
        // Build detailed message
        $message = "{$totalCancelled} sale(s) cancelled due to time limit.";
        
        return Notification::create([
            'type' => Notification::TYPE_SALE_CANCELLED,
            'title' => 'Sales Cancelled',
            'message' => rtrim($message),
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Employees
    // ------------------------------------------------------------
    public static function createEmployeeHiredNotification($company, $employeeProfile, $employee){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_HIRED,
            'title' => 'Employee Hired',
            'message' => "Employee {$employeeProfile->name} hired for a salary of {$employee->salary_month} and recruitment cost of {$employee->recruitment_cost}.",
            'url' => route('company.employees.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createEmployeeMoodDecreasedNotification($company, $employee){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_MOOD_DECREASED,
            'title' => 'Employee Mood Decreased',
            'message' => "Employee {$employee->name} mood decreased to {$employee->current_mood}. Consider promoting or firing him.",
            'url' => route('company.employees.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createEmployeeResignedNotification($company, $employee){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_RESIGNED,
            'title' => 'Employee Resigned',
            'message' => "Employee {$employee->name} resigned from {$employee->employeeProfile->name} with mood of {$employee->current_mood}.",
            'url' => route('company.employees.index'),
            'user_id' => $employee->company->user_id,
        ]);
    }

    public static function createEmployeeSalaryPaidNotification($company, $totalSalaries){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_SALARY_PAID,
            'title' => 'Employee Salary Paid',
            'message' => "Monthly employee salaries paid for DZD {$totalSalaries}.",
            'url' => route('company.employees.index'),	
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Machines
    // ------------------------------------------------------------
    public static function createMachineSetupNotification($company, $machine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_SETUP,
            'title' => 'Machine Setup',
            'message' => "Machine {$machine->name} setup successfully.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineAssignedEmployeeNotification($company, $machine, $employee){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_ASSIGNED_EMPLOYEE,
            'title' => 'Machine Assigned Employee',
            'message' => "Machine {$machine->name} assigned to employee {$employee->name}.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineProductionStartedNotification($company, $machine, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_PRODUCTION_STARTED,
            'title' => 'Machine Production Started',
            'message' => "Machine {$machine->name} production started for {$product->name} with quantity of {$quantity}.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineProductionCompletedNotification($company, $machine, $product, $quantity, $qualityFactor){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_PRODUCTION_COMPLETED,
            'title' => 'Machine Production Completed',
            'message' => "Machine {$machine->name} production completed for {$product->name} with quantity of {$quantity} and quality factor of {$qualityFactor}.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineOperationCostsPaidNotification($company, $totalCost){

        return Notification::create([
            'type' => Notification::TYPE_MACHINE_OPERATION_COSTS_PAID,
            'title' => 'Machine Operation Costs Paid',
            'message' => "Machine operation costs paid for DZD {$totalCost}.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineSoldNotification($company, $companyMachine, $soldPrice){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_SOLD,
            'title' => 'Machine Sold',
            'message' => "Machine {$companyMachine->machine->name} sold for DZD " . $soldPrice,
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Maintenance
    // ------------------------------------------------------------
    public static function createMachineBrokenNotification($company, $companyMachine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_BROKEN,
            'title' => 'Machine Broken',
            'message' => "Machine {$companyMachine->machine->name} broken. Any production orders for this machine will be cancelled and materials will be lost. The value of the machine is now DZD {$companyMachine->current_value}.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineReliabilityDecreasedNotification($company, $companyMachine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_RELIABILITY_DECREASED,
            'title' => 'Machine Reliability Decreased',
            'message' => "Machine {$companyMachine->machine->name} reliability decreased to {$companyMachine->current_reliability}. Consider running a predictive maintenance.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineMaintenanceStartedNotification($company, $companyMachine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_MAINTENANCE_STARTED,
            'title' => 'Machine Maintenance Started',
            'message' => "Machine {$companyMachine->machine->name} maintenance started.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineMaintenanceCompletedNotification($company, $companyMachine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_MAINTENANCE_COMPLETED,
            'title' => 'Machine Maintenance Completed',
            'message' => "Machine {$companyMachine->machine->name} maintenance completed.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Events
    // ------------------------------------------------------------
    public static function createCountriesImportBlockedNotification($countries){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_IMPORT_BLOCKED,
                'title' => 'Countries Import Blocked',
                'message' => "Because of political reasons, countries import blocked for " . implode(', ', $countries) . ".",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesImportAllowedNotification($countries){
        $companies = Company::get();
        
        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_IMPORT_ALLOWED,
                'title' => 'Countries Import Allowed',
                'message' => "All political reasons are resolved, relation with countries are restored and import is allowed for " . implode(', ', $countries) . ".",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOrmuzCanalClosedNotification($products, $countries, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_ORMUZ_CANAL_CLOSED,
                'title' => 'Ormuz Canal Closed',
                'message' => "Because of Israel Iran war, the ormuz canal is closed. Prices of oil, will increase causing oil based products: " . implode(', ', $products) . " to increase by " . $rate * 100 . "%, causing shipping costs and delays from " . implode(', ', $countries) . " to increase.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOrmuzCanalOpenedNotification($products, $countries)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_ORMUZ_CANAL_OPENED,
                'title' => 'Ormuz Canal Opened',
                'message' => "Ormuz canal reopened. Prices of oil, will be back to normal causing oil based products: " . implode(', ', $products) . " and also shipping costs and delays from " . implode(', ', $countries) . " will be back to normal.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createSuezCanalClosedNotification($countries, $rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_SUEZ_CANAL_CLOSED,
                'title' => 'Suez Canal Closed',
                'message' => "Suez canal closed for " . implode(', ', $countries) . ". This will affect the shipping costs and delivery time of your products from suppliers by " . $rate * 100 . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createSuezCanalOpenedNotification($countries){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_SUEZ_CANAL_OPENED,
                'title' => 'Suez Canal Opened',
                'message' => "Suez canal reopened for " . implode(', ', $countries) . ". The shipping costs and delivery time of your products from suppliers are back to normal.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createHeatWaveStartedNotification($products, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_HEAT_WAVE_STARTED,
                'title' => 'Heat Wave Started',
                'message' => "Heat wave started. You will lose " . $rate * 100 . "% of your products in inventory: " . implode(', ', $products) . ". Also your employees will be more tired and less productive and local suppliers will raise their prices.",
                'url' => route('company.inventory.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createHeatWaveEndedNotification($products, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_HEAT_WAVE_ENDED,
                'title' => 'Heat Wave Ended',
                'message' => "Heat wave ended. Your employees will be more productive and less tired and local suppliers will lower their prices.",
                'url' => route('company.inventory.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createHealthComplaintStartedNotification($rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_HEALTH_COMPLAINT_STARTED,
                'title' => 'Health Complaint Started',
                'message' => "Health complaint started which will cause demand decline by " . $rate * 100 . "%.",
                'url' => route('company.products.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createHealthComplaintEndedNotification(){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_HEALTH_COMPLAINT_ENDED,
                'title' => 'Health Complaint Ended',
                'message' => "Health complaint ended which will cause demand to be back to normal",
                'url' => route('company.products.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createWorkersProtestStartedNotification($rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_WORKERS_PROTEST_STARTED,
                'title' => 'Workers Protest Started',
                'message' => "Workers protest started after one of them did not receive injury compensation which will cause employees to be less productive and less efficient by " . $rate * 100 . "%.",
                'url' => route('company.products.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createWorkersProtestEndedNotification(){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_WORKERS_PROTEST_ENDED,
                'title' => 'Workers Protest Ended',
                'message' => "Workers protest ended and the employees are back to normal",
                'url' => route('company.products.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createWorkAccedentStartedNotification($company, $machine, $employee){
        return Notification::create([
            'type' => Notification::TYPE_WORK_ACCEDENT_STARTED,
            'title' => 'Work Accedent Started',
            'message' => "Work accedent started for {$machine->name}. Any production orders for this machine will be cancelled and materials will be lost. Employees will be less productive and less efficient because of employee {$employee->name} resign after injury.",
            'url' => route('company.machines.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Loans
    // ------------------------------------------------------------
    public static function createLoanBorrowedNotification($company, $loan){
        return Notification::create([
            'type' => Notification::TYPE_LOAN_BORROWED,
            'title' => 'Loan Borrowed', 
            'message' => "Loan borrowed from {$loan->bank->name} for DZD {$loan->amount} for {$loan->duration_months} months.",
            'url' => route('company.loans.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createLoanBorrowedInsufficientFundsNotification($company, $loan, $reason){
        return Notification::create([
            'type' => Notification::TYPE_LOAN_BORROWED_INSUFFICIENT_FUNDS,
            'title' => 'Loan Borrowed Insufficient Funds',
            'message' => "Insufficient funds to pay ". $reason." payments. A new loan has been borrowed to cover the monthly payments from {$loan->bank->name} for DZD {$loan->monthly_payment} for {$loan->duration_months} months.",
            'url' => route('company.loans.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createLoanPaidNotification($company, $loan){
        return Notification::create([
            'type' => Notification::TYPE_LOAN_PAID,
            'title' => 'Loan Paid',
            'message' => "Loan paid to {$loan->bank->name} for DZD {$loan->monthly_payment}.",
            'url' => route('company.loans.index'),
            'user_id' => $company->user_id,
        ]);
    }

    // ------------------------------------------------------------
    // Advertisers
    // ------------------------------------------------------------
    public static function createAdPackageCreatedNotification($company, $ad){   
        return Notification::create([
            'type' => Notification::TYPE_AD_PACKAGE_CREATED,
            'title' => 'Ad Package Created',
            'message' => "Ad package created for {$ad->product->name} by {$ad->advertiser->name} for DZD {$ad->price}.",
            'url' => route('company.ads.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createAdPackageCompletedNotification($company, $ad){
        return Notification::create([
            'type' => Notification::TYPE_AD_PACKAGE_COMPLETED,
            'title' => 'Ad Package Completed',
            'message' => "Ad package completed for {$ad->product->name} by {$ad->advertiser->name}.",
            'url' => route('company.ads.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function getUnreadCount()
    {
        return Notification::where('user_id', auth()->user()->id)->whereNull('read_at')->count();
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead()
    {
        return Notification::where('user_id', auth()->user()->id)->whereNull('read_at')->update([
            'read_at' => now(),
        ]);
    }
} 