<?php

namespace App\Services;

use App\Models\Bank;
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
            'type' => Transaction::TYPE_TECHNOLOGY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_PURCHASE,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);


        return $funds;
    }

    //-------------------------------------
    // Inventory
    //-------------------------------------
    public static function payInventoryCosts($company, $product, $totalCost){
        if($totalCost > $company->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalCost, "inventory costs");
        }

        $funds = $company->funds;
        $funds -= $totalCost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalCost,
            'type' => Transaction::TYPE_INVENTORY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_SALE_SHIPPING,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_SALE_PAYMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_EMPLOYEE_RECRUITMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    public static function payEmployeesSalary($company, $totalSalaries){
        if($totalSalaries > $company->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalSalaries, "employee salaries");
        }

        $funds = $company->funds;
        $funds -= $totalSalaries;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalSalaries,
            'type' => Transaction::TYPE_EMPLOYEE_SALARY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_MACHINE_SETUP,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    public static function payMachineOperationCost($company, $totalCost){
        if($totalCost > $company->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalCost, "machine operation costs");
        }

        $funds = $company->funds;
        $funds -= $totalCost;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalCost,
            'type' => Transaction::TYPE_MACHINE_OPERATIONS,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
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
            'type' => Transaction::TYPE_MAINTENANCE,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    //-------------------------------------
    // Loans
    //-------------------------------------
    public static function receiveLoan($company, $loanAmount, $loanId = null){
        $funds = $company->funds;
        $funds += $loanAmount;
        $unpaidLoans = $company->unpaid_loans;
        $unpaidLoans += $loanAmount;
        $company->update(['funds' => $funds, 'unpaid_loans' => $unpaidLoans]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $loanAmount,
            'type' => Transaction::TYPE_LOAN_RECEIVED,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    public static function payLoan($company, $paymentAmount){
        $funds = $company->funds;
        $funds -= $paymentAmount;
        $unpaidLoans = $company->unpaid_loans;
        $unpaidLoans -= $paymentAmount;
        $company->update(['funds' => $funds, 'unpaid_loans' => $unpaidLoans]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $paymentAmount,
            'type' => Transaction::TYPE_LOAN_PAYMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function receiveMachineSale($company, $soldPrice){
        $funds = $company->funds;
        $funds += $soldPrice;
        
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $soldPrice,
            'type' => Transaction::TYPE_MACHINE_SOLD,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }

    //-------------------------------------
    // Advertisers
    //-------------------------------------
    public static function payAdPackage($company, $ad){
        $funds = $company->funds;
        $funds -= $ad->price;
        $company->update(['funds' => $funds]);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $ad->price,
            'type' => Transaction::TYPE_MARKETING,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $funds;
    }
}