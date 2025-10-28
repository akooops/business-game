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
        $company->decrement('funds', $technology->research_cost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $technology->research_cost,
            'type' => Transaction::TYPE_TECHNOLOGY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;  
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function payPurchase($company, $purchase){
        $company->decrement('funds', $purchase->total_cost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $purchase->total_cost,
            'type' => Transaction::TYPE_PURCHASE,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);


        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Inventory
    //-------------------------------------
    public static function payInventoryCosts($company, $product, $totalCost){
        if($totalCost > $company->fresh()->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalCost, "inventory costs");
        }

        $company->decrement('funds', $totalCost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalCost,
            'type' => Transaction::TYPE_INVENTORY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    public static function receiveSalePayment($company, $sale){
        $company->increment('funds', $sale->sale_price * $sale->quantity);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $sale->sale_price * $sale->quantity,
            'type' => Transaction::TYPE_SALE_PAYMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Employees
    //-------------------------------------
    public static function payEmployeeRecruitmentCost($company, $employee){
        $company->decrement('funds', $employee->recruitment_cost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $employee->recruitment_cost,
            'type' => Transaction::TYPE_EMPLOYEE_RECRUITMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    public static function payEmployeesSalary($company, $totalSalaries){
        if($totalSalaries > $company->fresh()->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalSalaries, "employee salaries");
        }

        $company->decrement('funds', $totalSalaries);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalSalaries,
            'type' => Transaction::TYPE_EMPLOYEE_SALARY,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }   

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function payMachineSetupCost($company, $machine){
        $company->decrement('funds', $machine->cost_to_acquire);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $machine->cost_to_acquire,
            'type' => Transaction::TYPE_MACHINE_SETUP,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    public static function payMachineOperationCost($company, $totalCost){
        if($totalCost > $company->fresh()->funds){
            $randomBank = Bank::inRandomOrder()->first();

            LoansService::borrowMoney($company, $randomBank, $totalCost, "machine operation costs");
        }

        $company->decrement('funds', $totalCost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $totalCost,
            'type' => Transaction::TYPE_MACHINE_OPERATIONS,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Maintenance
    //-------------------------------------
    public static function payMaintenanceCost($company, $maintenance){
        $company->decrement('funds', $maintenance->maintenances_cost);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $maintenance->maintenances_cost,
            'type' => Transaction::TYPE_MAINTENANCE,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Loans
    //-------------------------------------
    public static function receiveLoan($company, $loanAmount, $loanId = null){
        $company->increment('funds', $loanAmount);
        $company->increment('unpaid_loans', $loanAmount);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $loanAmount,
            'type' => Transaction::TYPE_LOAN_RECEIVED,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    public static function payLoan($company, $paymentAmount){
        $company->decrement('funds', $paymentAmount);
        $company->decrement('unpaid_loans', $paymentAmount);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $paymentAmount,
            'type' => Transaction::TYPE_LOAN_PAYMENT,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function receiveMachineSale($company, $soldPrice){
        $company->increment('funds', $soldPrice);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $soldPrice,
            'type' => Transaction::TYPE_MACHINE_SOLD,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }

    //-------------------------------------
    // Advertisers
    //-------------------------------------
    public static function payAdPackage($company, $ad){
        $company->decrement('funds', $ad->price);

        Transaction::create([
            'company_id' => $company->id,
            'amount' => $ad->price,
            'type' => Transaction::TYPE_MARKETING,
            'transaction_at' => SettingsService::getCurrentTimestamp(),
        ]);

        return $company->fresh()->funds;
    }
}