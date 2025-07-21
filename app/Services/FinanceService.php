<?php

namespace App\Services;

class FinanceService
{
    public static function haveSufficientFunds($company, $amount){
        return $company->funds >= $amount;
    }

    public static function payTechnologyResearch($company, $technology){
        $funds = $company->funds;
        $funds -= $technology->research_cost;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $technology->research_cost);

        return $funds;  
    }

    public static function payPurchase($company, $purchase){
        $funds = $company->funds;
        $funds -= $purchase->total_cost;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $purchase->total_cost);

        return $funds;
    }

    public static function paySaleShippingCost($company, $sale){
        $funds = $company->funds;
        $funds -= $sale->shipping_cost * $sale->quantity;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $sale->shipping_cost * $sale->quantity);

        return $funds;
    }

    public static function receiveSalePayment($company, $sale){
        $funds = $company->funds;
        $funds += $sale->sale_price * $sale->quantity;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $sale->sale_price * $sale->quantity);

        return $funds;
    }

    public static function payInventoryCosts($company, $product, $leftAvailableStock){
        $funds = $company->funds;
        $funds -= $product->storage_cost * $leftAvailableStock;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $product->storage_cost * $leftAvailableStock);

        return $funds;
    }

    public static function payEmployeeRecruitmentCost($company, $employee){
        $funds = $company->funds;
        $funds -= $employee->employeeProfile->real_recruitment_cost;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $employee->employeeProfile->real_recruitment_cost);

        return $funds;
    }

    public static function payEmployeesSalary($company, $totalSalaries){
        $funds = $company->funds;
        $funds -= $totalSalaries;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $totalSalaries);

        return $funds;
    }   

    public static function payMachineSetupCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->cost_to_acquire;
        $company->update(['funds' => $funds]);

        NotificationService::createFinanceFundsChangedNotification($company, $machine->cost_to_acquire);

        return $funds;
    }

    public static function payMachineOperationCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->operation_cost;
        $company->update(['funds' => $funds]);

        return $funds;
    }
}