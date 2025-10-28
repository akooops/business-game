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

        // Prevent division by zero
        if ($maxNetWorth <= 0) $maxNetWorth = 1;
        if ($maxCarbon <= 0) $maxCarbon = 1;
        if ($maxResearch <= 0) $maxResearch = 1;

        // Calculate scores and add to company collection
        foreach ($companies as $company) {            
            // Normalize values (0-1 range)
            $normalizedNetWorth = ($company->funds - $company->unpaid_loans) / $maxNetWorth;
            $normalizedCarbon = $company->carbon_footprint / $maxCarbon;
            $normalizedResearch = $company->research_level / $maxResearch;
            
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