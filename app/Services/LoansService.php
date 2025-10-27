<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Loan;

class LoansService
{
    public static function borrowMoney($company, $bank, $amount, $reason = null){
        $totalAmount = $amount * (1 + $bank->loan_interest_rate);
        $monthlyPayment = $totalAmount / $bank->loan_duration_months;

        // Get current timestamp
        $borrowedAt = SettingsService::getCurrentTimestamp();

        $loan = Loan::create([
            'amount' => $amount,
            'interest_rate' => $bank->loan_interest_rate,
            'duration_months' => $bank->loan_duration_months,
            'total_amount' => $totalAmount,
            'remaining_amount' => $totalAmount,
            'monthly_payment' => $monthlyPayment,
            'borrowed_at' => $borrowedAt,
            'company_id' => $company->id,
            'bank_id' => $bank->id,
        ]);

        FinanceService::receiveLoan($company, $amount, $loan->id);

        if($reason){
            NotificationService::createLoanBorrowedInsufficientFundsNotification($company, $loan, $reason);
        }else{
            NotificationService::createLoanBorrowedNotification($company, $loan);
        }
    }

    public static function processMonthlyPayment($company){
        $currentTimestamp = SettingsService::getCurrentTimestamp();
        
        // Only process loans where at least 30 days (1 month) have passed since borrowed_at
        $oneMonthAgo = $currentTimestamp->copy()->subDays(30);
        
        $loans = $company->loans()
            ->where('paid_at', null)
            ->where('borrowed_at', '<=', $oneMonthAgo)
            ->get();

        foreach($loans as $loan){
            $loan->update(['remaining_amount' => $loan->remaining_amount - $loan->monthly_payment]);

            if($loan->remaining_amount <= 0){
                $loan->update([
                    'paid_at' => $currentTimestamp,
                ]);
            }

            if($company->funds < $loan->monthly_payment){
                $randomBank = Bank::inRandomOrder()->first();
                self::borrowMoney($company, $randomBank, $loan->monthly_payment, "existing monthly loan payments");
            }

            FinanceService::payLoan($company, $loan->monthly_payment);
            NotificationService::createLoanPaidNotification($company, $loan);
        }
    }

    public static function payLoan($company, $loan){
        $loan->update([
            'paid_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::payLoan($company, $loan->remaining_amount);
        NotificationService::createLoanPaidNotification($company, $loan);
    }
}