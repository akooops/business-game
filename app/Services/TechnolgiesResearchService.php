<?php

namespace App\Services;

use App\Models\CompanyProduct;
use App\Models\CompanyTechnology;
use App\Models\Technology;

class TechnolgiesResearchService
{
    public static function researchTechnology($company, $technology){
        // Pay funds
        $funds = FinanceService::payTechnologyResearch($company, $technology);

        // Get current timestamp
        $startedAt = SettingsService::getCurrentTimestamp();
        $estimatedCompletedAt = $startedAt->copy()->addDays($technology->research_time_days);

        $companyTechnology = CompanyTechnology::create([
            'research_cost' => $technology->research_cost,
            'research_time_days' => $technology->research_time_days,
            'company_id' => $company->id,
            'technology_id' => $technology->id,
            'started_at' => $startedAt,
            'estimated_completed_at' => $estimatedCompletedAt,
        ]);

        // Check if company has unlocked all technologies at current level
        $currentLevel = $company->research_level;
        
        // Get all technologies at current level
        $technologiesAtCurrentLevel = Technology::where('level', $currentLevel)->pluck('id');
        
        // Get company's completed technologies at current level
        $companyCompletedTechnologies = $company->companyTechnologies()
            ->whereHas('technology', function($query) use ($currentLevel) {
                $query->where('level', $currentLevel);
            })
            ->pluck('technology_id');
        
        // Check if company has completed all technologies at current level
        if ($technologiesAtCurrentLevel->count() > 0 && 
            $companyCompletedTechnologies->count() >= $technologiesAtCurrentLevel->count()) {
            
            // Increment research level
            $company->update([
                'research_level' => $currentLevel + 1
            ]);
        }

        NotificationService::createTechnologyResearchStartedNotification($companyTechnology);

        return $companyTechnology;  
    }

    public static function completedResearch($companyTechnology){
        $companyTechnology->update([
            'completed_at' => SettingsService::getCurrentTimestamp(),
        ]);

        // Create products for company
        $products = $companyTechnology->technology->products;
        
        foreach($products as $product){
            // Check if product already exists
            $companyProduct = $companyTechnology->company->companyProducts()->where('product_id', $product->id)->first();
            if($companyProduct){
                continue;
            }

            CompanyProduct::create([
                'company_id' => $companyTechnology->company_id,
                'product_id' => $product->id,
                'available_stock' => 0,
                'sale_price' => SalesService::getCurrentGameweekProductMarketPrice($product),
            ]);
        }

        NotificationService::createTechnologyResearchCompletedNotification($companyTechnology);
    }

    public static function validateTechnologyResearch($company, $technology){
        $errors = [];
        
        // Check if company has sufficient funds
        if (!FinanceService::haveSufficientFunds($company, $technology->research_cost)) {
            $errors['funds'] = 'You do not have enough funds to research this technology. Required: DZD ' . $technology->research_cost . ', Available: DZD ' . $company->funds;
        }
        
        // Check if technology exists
        if (!$technology) {
            $errors['technology_id'] = 'The selected technology does not exist.';
            return $errors;
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