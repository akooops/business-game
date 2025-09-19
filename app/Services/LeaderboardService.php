<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyTechnology;

class LeaderboardService
{
    /**
     * Calculate leaderboard with normalized scores
     * Formula: (normalized_funds - normalized_loans - normalized_carbon + normalized_research) * weights
     */
    public static function getLeaderboard($withWeights = true)
    {
        $companies = Company::with(['user', 'companyTechnologies.technology'])
            ->whereHas('user') // Only companies with users
            ->get();

        if ($companies->isEmpty()) {
            return [];
        }

        // Calculate raw scores for each company
        $companyData = $companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'user_name' => $company->user->name ?? 'Unknown',
                'funds' => $company->funds,
                'unpaid_loans' => $company->unpaid_loans,
                'carbon_footprint' => $company->carbon_footprint,
                'research_level' => self::calculateResearchLevel($company),
                'completed_technologies' => $company->companyTechnologies()
                    ->whereNotNull('completed_at')
                    ->count(),
            ];
        })->toArray();

        // Get max values for normalization
        $maxValues = self::getMaxValues($companyData);

        // Calculate normalized scores
        $leaderboard = array_map(function ($company) use ($maxValues, $withWeights) {
            $normalizedScores = self::calculateNormalizedScores($company, $maxValues);
            $finalScore = self::calculateFinalScore($normalizedScores, $withWeights);

            return array_merge($company, [
                'normalized_funds' => $normalizedScores['funds'],
                'normalized_loans' => $normalizedScores['loans'],
                'normalized_carbon' => $normalizedScores['carbon'],
                'normalized_research' => $normalizedScores['research'],
                'final_score' => $finalScore,
                'rank' => 0, // Will be set after sorting
            ]);
        }, $companyData);

        // Sort by final score (highest first) and assign ranks
        usort($leaderboard, function ($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });

        // Assign ranks
        foreach ($leaderboard as $index => &$company) {
            $company['rank'] = $index + 1;
        }

        return $leaderboard;
    }

    /**
     * Calculate research level based on completed technologies
     */
    private static function calculateResearchLevel($company)
    {
        $completedTechnologies = $company->companyTechnologies()
            ->whereNotNull('completed_at')
            ->with('technology')
            ->get();

        if ($completedTechnologies->isEmpty()) {
            return 0;
        }

        // Calculate weighted research level based on technology levels
        $totalResearchValue = $completedTechnologies->sum(function ($companyTech) {
            $technology = $companyTech->technology;
            
            // Weight by technology level (higher level = more valuable)
            $levelWeight = $technology->level ?? 1;
            
            // Could also weight by research cost (more expensive = more valuable)
            $costWeight = ($technology->research_cost ?? 0) / 1000; // Normalize cost
            
            return $levelWeight + $costWeight;
        });

        return round($totalResearchValue, 2);
    }

    /**
     * Get maximum values for normalization
     */
    private static function getMaxValues($companyData)
    {
        return [
            'funds' => max(array_column($companyData, 'funds')) ?: 1,
            'loans' => max(array_column($companyData, 'unpaid_loans')) ?: 1,
            'carbon' => max(array_column($companyData, 'carbon_footprint')) ?: 1,
            'research' => max(array_column($companyData, 'research_level')) ?: 1,
        ];
    }

    /**
     * Calculate normalized scores (0-1 range)
     */
    private static function calculateNormalizedScores($company, $maxValues)
    {
        return [
            'funds' => $company['funds'] / $maxValues['funds'],
            'loans' => $company['unpaid_loans'] / $maxValues['loans'],
            'carbon' => $company['carbon_footprint'] / $maxValues['carbon'],
            'research' => $company['research_level'] / $maxValues['research'],
        ];
    }

    /**
     * Calculate final weighted score
     * Formula: (funds - loans - carbon + research) with optional weights
     */
    private static function calculateFinalScore($normalizedScores, $withWeights = true)
    {
        if ($withWeights) {
            // Configurable weights
            $weights = [
                'funds' => 0.35,      // 35% weight
                'loans' => 0.25,      // 25% weight (negative)
                'carbon' => 0.20,     // 20% weight (negative)
                'research' => 0.20,   // 20% weight
            ];

            $score = 
                ($normalizedScores['funds'] * $weights['funds']) +
                (-$normalizedScores['loans'] * $weights['loans']) +
                (-$normalizedScores['carbon'] * $weights['carbon']) +
                ($normalizedScores['research'] * $weights['research']);
        } else {
            // Simple formula without weights
            $score = 
                $normalizedScores['funds'] - 
                $normalizedScores['loans'] - 
                $normalizedScores['carbon'] + 
                $normalizedScores['research'];
        }

        return round($score, 4);
    }

    /**
     * Get leaderboard statistics
     */
    public static function getLeaderboardStats()
    {
        $companies = Company::whereHas('user')->get();
        
        if ($companies->isEmpty()) {
            return null;
        }

        return [
            'total_companies' => $companies->count(),
            'total_funds' => $companies->sum('funds'),
            'total_loans' => $companies->sum('unpaid_loans'),
            'total_carbon' => $companies->sum('carbon_footprint'),
            'avg_funds' => $companies->avg('funds'),
            'avg_loans' => $companies->avg('unpaid_loans'),
            'avg_carbon' => $companies->avg('carbon_footprint'),
            'top_funded_company' => $companies->sortByDesc('funds')->first(),
            'most_research' => self::getMostResearchedCompany($companies),
        ];
    }

    /**
     * Get company with most research
     */
    private static function getMostResearchedCompany($companies)
    {
        $bestCompany = null;
        $highestResearch = 0;

        foreach ($companies as $company) {
            $researchLevel = self::calculateResearchLevel($company);
            if ($researchLevel > $highestResearch) {
                $highestResearch = $researchLevel;
                $bestCompany = $company;
            }
        }

        return $bestCompany;
    }

    /**
     * Get historical leaderboard data for trends
     */
    public static function getLeaderboardTrends($days = 7)
    {
        // For now, return current leaderboard
        // In future, could store historical snapshots
        return [
            'current' => self::getLeaderboard(),
            'previous' => [], // Could implement historical tracking
        ];
    }
}
