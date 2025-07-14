<?php

namespace App\Services;

use App\Models\CompanyTechnology;
use App\Models\Technology;

class TechnolgiesResearchService
{
    public static function researchTechnology($company, $technology){
        // Pay funds
        $funds = FundsService::payTechnologyResearch($company, $technology);

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

        return $companyTechnology;  
    }

    public static function completedResearch($companyTechnology){
        $companyTechnology->update([
            'completed_at' => SettingsService::getCurrentTimestamp(),
        ]);
    }
}