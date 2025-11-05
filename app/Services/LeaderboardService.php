<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;

class LeaderboardService
{
    /**
     * Get companies with normalized scores
     * Formula: (Revenue × 35%) + (Unpaid Loans Inverse × 15%) 
     *          + (Activity × 25%) + (Research × 15%) + (Carbon Inverse × 10%)
     * Revenue excludes loans (operational only)
     * Final Score = Score × 10
     */
    public static function getLeaderboard()
    {
        $companies = Company::with(['user'])
            ->withCount([
                'transactions',
                'sales',
                'purchases',
                'productionOrders' => function($query) {
                    $query->where('production_orders.status', 'completed');
                },
                'employees' => function($query) {
                    $query->where('status', 'active');
                },
                'companyMachines' => function($query) {
                    $query->where('status', '!=', 'sold');
                }
            ])
            ->get();

        if($companies->isEmpty()) {
            return $companies;
        }

        // Calculate all metrics first
        $revenues = [];
        $unpaidLoans = [];
        $activityScores = [];
        $researchLevels = [];
        $carbonFootprints = [];
        
        foreach ($companies as $company) {
            // Revenue (without loans)
            $totalRevenue = Transaction::where('company_id', $company->id)
                ->whereIn('type', [
                    Transaction::TYPE_SALE_PAYMENT,
                    Transaction::TYPE_MACHINE_SOLD
                ])
                ->sum('amount');
            
            // Activity score (weighted combination)
            $activityScore = (
                ($company->sales_count * 0.30) +
                ($company->production_orders_count * 0.30) +
                ($company->purchases_count * 0.20) +
                ($company->employees_count * 0.10) +
                ($company->company_machines_count * 0.10)
            );
            
            $revenues[] = $totalRevenue;
            $unpaidLoans[] = $company->unpaid_loans;
            $activityScores[] = $activityScore;
            $researchLevels[] = $company->research_level;
            $carbonFootprints[] = $company->carbon_footprint;
        }
        
        // Get max/min values for normalization
        $minRevenue = min($revenues);
        $maxRevenue = max($revenues);
        $revenueRange = $maxRevenue - $minRevenue;
        
        $minUnpaidLoans = min($unpaidLoans);
        $maxUnpaidLoans = max($unpaidLoans);
        $unpaidLoansRange = $maxUnpaidLoans - $minUnpaidLoans;
        
        $maxActivityScore = max($activityScores) ?: 1;
        $maxResearch = max($researchLevels) ?: 1;
        $maxCarbon = max($carbonFootprints) ?: 1;

        // Calculate scores and add to company collection
        foreach ($companies as $company) {
            // Revenue (without loans)
            $totalRevenue = Transaction::where('company_id', $company->id)
                ->whereIn('type', [
                    Transaction::TYPE_SALE_PAYMENT,
                    Transaction::TYPE_MACHINE_SOLD
                ])
                ->sum('amount');
            
            // Activity score
            $activityScore = (
                ($company->sales_count * 0.30) +
                ($company->production_orders_count * 0.30) +
                ($company->purchases_count * 0.20) +
                ($company->employees_count * 0.10) +
                ($company->company_machines_count * 0.10)
            );
            
            // Normalize values (0-1 range)
            // Revenue: offset normalization to handle negatives
            $normalizedRevenue = $revenueRange > 0 ? ($totalRevenue - $minRevenue) / $revenueRange : 0.5;
            
            // Unpaid Loans: inverse (lower is better)
            $normalizedUnpaidLoans = $unpaidLoansRange > 0 ? ($company->unpaid_loans - $minUnpaidLoans) / $unpaidLoansRange : 0.5;
            $unpaidLoansInverse = 1 - $normalizedUnpaidLoans;
            
            // Activity
            $normalizedActivity = $maxActivityScore > 0 ? $activityScore / $maxActivityScore : 0;
            
            // Research
            $normalizedResearch = $maxResearch > 0 ? $company->research_level / $maxResearch : 0;
            
            // Carbon: inverse (lower is better)
            $normalizedCarbon = $maxCarbon > 0 ? $company->carbon_footprint / $maxCarbon : 0;
            $carbonInverse = 1 - $normalizedCarbon;
            
            // Calculate final score
            // Revenue × 35% + Unpaid Loans Inverse × 15% 
            // + Activity × 25% + Research × 15% + Carbon Inverse × 10%
            $score = ($normalizedRevenue * 0.35)
                   + ($unpaidLoansInverse * 0.15)
                   + ($normalizedActivity * 0.25)
                   + ($normalizedResearch * 0.15)
                   + ($carbonInverse * 0.10);
            
            // Multiply by 10 for final score (0-10 range)
            $finalScore = $score * 10;

            // Add all calculated fields to company object for display
            $company->score = round($finalScore, 4);
            $company->revenue = round($totalRevenue, 2);
            $company->activity_score = round($activityScore, 2);
            $company->normalized_revenue = round($normalizedRevenue, 4);
            $company->normalized_unpaid_loans_inverse = round($unpaidLoansInverse, 4);
            $company->normalized_activity = round($normalizedActivity, 4);
            $company->normalized_research = round($normalizedResearch, 4);
            $company->normalized_carbon_inverse = round($carbonInverse, 4);
        }

        // Sort companies by score (highest first)
        $companies = $companies->sortByDesc('score')->values();

        return $companies;
    }
}