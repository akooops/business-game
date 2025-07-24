<?php

namespace App\Services;

class ValidationService
{
    public static function validateTechnologyResearch($company, $technology){
        $errors = [];

        // Check if company has sufficient funds
        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $technology->research_cost);
        if(!$hasSufficientFunds){
            $errors['funds'] = 'You do not have enough funds to research this technology.';
        }

        // Check if company is already researching this technology
        $alreadyResearching = $company->companyTechnologies()
            ->where('technology_id', $technology->id)
            ->exists();
        
        if ($alreadyResearching) {
            $errors['technology_id'] = 'You are already researching this technology.';
            return $errors;
        }

        // Check research level
        if ($technology->level > $company->research_level + 1) {
            $errors['technology_id'] = 'You can only research technologies up to level ' . ($company->research_level + 1) . '.';
        }

        return $errors;
    }
}