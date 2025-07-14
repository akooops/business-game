<?php

namespace App\Services;

class FundsService
{
    public static function payTechnologyResearch($company, $technology){
        $funds = $company->funds;
        $funds -= $technology->research_cost;
        $company->update(['funds' => $funds]);

        return $funds;  
    }
}