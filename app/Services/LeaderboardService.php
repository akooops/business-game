<?php

namespace App\Services;

use App\Models\Company;

class LeaderboardService
{
    /**
     * Get companies with normalized scores
     * Formula: (Net Worth / Max Net Worth) × 0.5 - (Carbon / Max Carbon) × 0.25 + (Research / Max Research) × 0.25
     * Where Net Worth = Company Funds - Company Loans
     */
    public static function getLeaderboard()
    {
        $companies = Company::with('user')->get();

        foreach ($companies as $company) {
            $netWorth = $company->funds - $company->unpaid_loans;
            $netWorths[] = $netWorth;
        }

        if(empty($netWorths)) {
            return $companies;
        }

        // Get max values for normalization
        $maxNetWorth = max($netWorths);

        $maxCarbon = Company::max('carbon_footprint');
        $maxResearch = Company::max('research_level');

        // Calculate scores and add to company collection
        foreach ($companies as $company) {            
            // Normalize values (0-1 range) - avoid division by zero
            $normalizedNetWorth = $maxNetWorth > 0 ? ($company->funds - $company->unpaid_loans) / $maxNetWorth : 0;
            $normalizedCarbon = $maxCarbon > 0 ? $company->carbon_footprint / $maxCarbon : 0;
            $normalizedResearch = $maxResearch > 0 ? $company->research_level / $maxResearch : 0;
            
            // Calculate final score: 50% net worth - 25% carbon + 25% research
            $score = ($normalizedNetWorth * 0.5) - ($normalizedCarbon * 0.25) + ($normalizedResearch * 0.25);

            // Add score to company object
            $company->score = round($score, 4);
        }

        // Sort companies by score (highest first)
        $companies = $companies->sortByDesc('score')->values();

        return $companies;
    }
}