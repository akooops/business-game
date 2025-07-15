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
}