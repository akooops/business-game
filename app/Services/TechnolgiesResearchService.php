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
                'total_stock' => 0,
                'in_sale_stock' => 0,
                'sale_price' => SalesService::getCurrentGameweekProductMarketPrice($product),
            ]);
        }

        NotificationService::createTechnologyResearchCompletedNotification($companyTechnology);
    }
}