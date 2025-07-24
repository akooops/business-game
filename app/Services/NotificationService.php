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

    // ------------------------------------------------------------
    // Employees
    // ------------------------------------------------------------
    public static function createEmployeeHiredNotification($company, $employeeProfile, $employee){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_HIRED,
            'title' => 'Employee Hired',
            'message' => "Employee {$employeeProfile->name} hired for {$employee->salary_month}.",
            'url' => route('company.employees.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createEmployeeMoodDecreasedNotification($employee){
        return Notification::create([
            'type' => Notification::TYPE_EMPLOYEE_MOOD_DECREASED,
            'title' => 'Employee Mood Decreased',
            'message' => "Employee {$employee->name} mood decreased to {$employee->current_mood}. Consider promoting or firing him.",
            'url' => route('company.employees.index'),
            'user_id' => $employee->company->user_id,
        ]);
    }

    public static function createEmployeeResignedNotification($employee){
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
            'message' => "Employee salary paid for DZD {$totalSalaries}.",
            'url' => route('company.employees.index'),	
            'user_id' => $company->user_id,
        ]);
    }

    public static function createMachineSetupNotification($company, $machine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_SETUP,
            'title' => 'Machine Setup',
            'message' => "Machine {$machine->name} setup.",
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

    public static function createMachineBrokenNotification($company, $companyMachine){
        return Notification::create([
            'type' => Notification::TYPE_MACHINE_BROKEN,
            'title' => 'Machine Broken',
            'message' => "Machine {$companyMachine->machine->name} broken.",
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
                'message' => "Countries import blocked for " . implode(', ', $countries) . ".",
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
                'message' => "Countries import allowed for " . implode(', ', $countries) . ".",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesCustomsDutiesRateRaisedNotification($countries, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_RAISED,
                'title' => 'Countries Customs Duties Rate Raised',
                'message' => "Countries customs duties rate raised for " . implode(', ', $countries) . " with " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesCustomsDutiesRateLoweredNotification($countries, $rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_LOWERED,
                'title' => 'Countries Customs Duties Rate Lowered',
                'message' => "Countries customs duties rate lowered for " . implode(', ', $countries) . " with " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOilPriceRaisedNotification($rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_OIL_PRICE_RAISED,
                'title' => 'Oil Price Raised',
                'message' => "Oil price raised with " . $rate . "%. This will affect the shipping costs of your products from suppliers and to customers.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOilPriceLoweredNotification($rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_OIL_PRICE_LOWERED,
                'title' => 'Oil Price Lowered',
                'message' => "Oil price lowered with " . $rate . "%. This will affect the shipping costs of your products from suppliers and to customers.",
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
                'message' => "Suez canal closed for " . implode(', ', $countries) . ". This will affect the shipping costs and delivery time of your products from suppliers by " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createSuezCanalOpenedNotification($countries, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_SUEZ_CANAL_OPENED,
                'title' => 'Suez Canal Opened',
                'message' => "Suez canal opened for " . implode(', ', $countries) . ". This will affect the shipping costs and delivery time of your products from suppliers by " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createInventoryDamagedNotification($company, $rate){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_DAMAGED,
            'title' => 'Inventory Damaged',
            'message' => "Inventory damaged with {$rate}%. You will lose {$rate}% of your products in inventory.",
            'url' => route('company.inventory.index'),
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