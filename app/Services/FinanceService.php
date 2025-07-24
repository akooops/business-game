<?php

namespace App\Services;

class FinanceService
{
    public static function haveSufficientFunds($company, $amount){
        return $company->funds >= $amount;
    }

    //-------------------------------------
    // Technologies
    //-------------------------------------
    public static function payTechnologyResearch($company, $technology){
        $funds = $company->funds;
        $funds -= $technology->research_cost;
        $company->update(['funds' => $funds]);

        return $funds;  
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function payPurchase($company, $purchase){
        $funds = $company->funds;
        $funds -= $purchase->total_cost;
        $company->update(['funds' => $funds]);


        return $funds;
    }

    //-------------------------------------
    // Inventory
    //-------------------------------------
    public static function payInventoryCosts($company, $product, $totalCost){
        $funds = $company->funds;
        $funds -= $totalCost;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    //-------------------------------------
    // Sales
    //-------------------------------------
    public static function paySaleShippingCost($company, $sale){
        $funds = $company->funds;
        $funds -= $sale->shipping_cost * $sale->quantity;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    public static function receiveSalePayment($company, $sale){
        $funds = $company->funds;
        $funds += $sale->sale_price * $sale->quantity;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    //-------------------------------------
    // Employees
    //-------------------------------------
    public static function payEmployeeRecruitmentCost($company, $employee){
        $funds = $company->funds;
        $funds -= $employee->recruitment_cost;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    public static function payEmployeesSalary($company, $totalSalaries){
        $funds = $company->funds;
        $funds -= $totalSalaries;
        $company->update(['funds' => $funds]);

        return $funds;
    }   

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function payMachineSetupCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->cost_to_acquire;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    public static function payMachineOperationCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->operations_cost;
        $company->update(['funds' => $funds]);

        return $funds;
    }

    //-------------------------------------
    // Maintenance
    //-------------------------------------
    public static function payMaintenanceCost($company, $maintenance){
        $funds = $company->funds;
        $funds -= $maintenance->maintenances_cost;
        $company->update(['funds' => $funds]);

        return $funds;
    }
}