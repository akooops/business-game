<?php

namespace App\Services;

use App\Models\Transaction;

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

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $technology->research_cost,
            'type' => 'technology',
            'reference_id' => $technology->id,
            'reference_type' => 'technology',
        ]);

        return $funds;  
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function payPurchase($company, $purchase){
        $funds = $company->funds;
        $funds -= $purchase->total_cost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $purchase->total_cost,
            'type' => 'purchase',
            'reference_id' => $purchase->id,
            'reference_type' => 'purchase',
        ]);


        return $funds;
    }

    //-------------------------------------
    // Inventory
    //-------------------------------------
    public static function payInventoryCosts($company, $product, $totalCost){
        $funds = $company->funds;
        $funds -= $totalCost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalCost,
            'type' => 'inventory',
        ]);

        return $funds;
    }

    //-------------------------------------
    // Sales
    //-------------------------------------
    public static function paySaleShippingCost($company, $sale){
        $funds = $company->funds;
        $funds -= $sale->shipping_cost * $sale->quantity;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $sale->shipping_cost * $sale->quantity,
            'type' => 'sale_shipping',
            'reference_id' => $sale->id,
            'reference_type' => 'sale',
        ]);


        return $funds;
    }

    public static function receiveSalePayment($company, $sale){
        $funds = $company->funds;
        $funds += $sale->sale_price * $sale->quantity;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $sale->sale_price * $sale->quantity,
            'type' => 'sale_payment',
            'reference_id' => $sale->id,
            'reference_type' => 'sale',
        ]);

        return $funds;
    }

    //-------------------------------------
    // Employees
    //-------------------------------------
    public static function payEmployeeRecruitmentCost($company, $employee){
        $funds = $company->funds;
        $funds -= $employee->recruitment_cost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $employee->recruitment_cost,
            'type' => 'employee_recruitment',
            'reference_id' => $employee->id,
            'reference_type' => 'employee',
        ]);

        return $funds;
    }

    public static function payEmployeesSalary($company, $totalSalaries){
        $funds = $company->funds;
        $funds -= $totalSalaries;
        $company->update(['funds' => $funds]);


        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalSalaries,
            'type' => 'employee_salary',
        ]);

        return $funds;
    }   

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function payMachineSetupCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->cost_to_acquire;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $machine->cost_to_acquire,
            'type' => 'machine_setup',
            'reference_id' => $machine->id,
            'reference_type' => 'machine',
        ]);

        return $funds;
    }

    public static function payMachineOperationCost($company, $machine){
        $funds = $company->funds;
        $funds -= $machine->operations_cost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $machine->operations_cost,
            'type' => 'machine_operations',
            'reference_id' => $machine->id,
            'reference_type' => 'machine',
        ]);

        return $funds;
    }

    //-------------------------------------
    // Maintenance
    //-------------------------------------
    public static function payMaintenanceCost($company, $maintenance){
        $funds = $company->funds;
        $funds -= $maintenance->maintenances_cost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $maintenance->maintenances_cost,
            'type' => 'maintenance',
            'reference_id' => $maintenance->id,
            'reference_type' => 'maintenance',
        ]);

        return $funds;
    }

    //-------------------------------------
    // Loans
    //-------------------------------------
    public static function receiveLoan($company, $loanAmount, $loanId = null){
        $funds = $company->funds;
        $funds += $loanAmount;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $loanAmount,
            'type' => 'loan_received',
            'reference_id' => $loanId,
            'reference_type' => 'loan',
        ]);

        return $funds;
    }

    public static function payLoan($company, $paymentAmount, $loanId = null){
        $funds = $company->funds;
        $funds -= $paymentAmount;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $paymentAmount,
            'type' => 'loan_payment',
            'reference_id' => $loanId,
            'reference_type' => 'loan',
        ]);

        return $funds;
    }
}