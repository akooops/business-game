<?php

namespace App\Services;

use App\Models\CompanyProduct;
use App\Models\CompanyTechnology;
use App\Models\Technology;

class TechnolgiesResearchService
{
    public static function researchTechnology($company, $technology){
        // Get current timestamp
        $startedAt = SettingsService::getCurrentTimestamp();

        $companyTechnology = CompanyTechnology::create([
            'research_cost' => $technology->research_cost,
            'research_time_days' => $technology->research_time_days,
            'started_at' => $startedAt,
            'company_id' => $company->id,
            'technology_id' => $technology->id,
        ]);

        FinanceService::payTechnologyResearch($company, $technology);
        NotificationService::createTechnologyResearchStartedNotification($company, $technology, $companyTechnology);
    }

    public static function processCompletedResearch($company){
        $companyTechnologies = $company->companyTechnologies()->where('completed_at', null)->get();

        foreach($companyTechnologies as $companyTechnology){
            // Check if technology research is completed
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $completedAt = $companyTechnology->started_at->copy()->addDays($companyTechnology->research_time_days);

            if($completedAt <= $currentTimestamp){
                // Update company technology as completed
                $companyTechnology->update([
                    'completed_at' => $currentTimestamp,
                ]);

                // Unlock products for company
                self::unlockProductsForCompany($company, $companyTechnology->technology);
                NotificationService::createTechnologyResearchCompletedNotification($company, $companyTechnology->technology, $companyTechnology);
            }
        }
    }

    private static function unlockProductsForCompany($company, $technology){
        $products = $technology->products;
    
        foreach($products as $product){
            // Check if product already exists
            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();

            if($companyProduct){
                continue;
            }

            // Create product for company
            CompanyProduct::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'available_stock' => 0,
                'sale_price' => SalesService::getCurrentGameweekProductMarketPrice($product),
            ]);
        }

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
    }
}