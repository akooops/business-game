<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the company dashboard with optimized stats
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        
        // Simple cache key based on company and current minute
        $cacheKey = "dashboard_stats_{$company->id}_" . now()->format('Y-m-d_H:i');
        
        // Cache for 1 minute to reduce database load
        $dashboardData = cache()->remember($cacheKey, 60, function() use ($company) {
            return StatsService::getCompanyStats($company);
        });
        
        // If it's an AJAX request, return JSON
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json($dashboardData);
        }

        // Return Inertia view with data
        return inertia('Company/Dashboard/Index', [
            'stats' => $dashboardData['current'],
            'trends' => $dashboardData['trends']
        ]);
    }

    /**
     * Get historical data for specific stat type
     *
     * @return \Illuminate\Http\Response
     */
    public function historical(Request $request)
    {
        $company = auth()->user()->company;
        $days = $request->query('days', 30);
        
        $trends = StatsService::getTrendData($company, $days);
        
        return response()->json([
            'trends' => $trends
        ]);
    }

    /**
     * Get current stats only (for real-time updates)
     *
     * @return \Illuminate\Http\Response
     */
    public function current(Request $request)
    {
        $company = auth()->user()->company;
        
        $stats = [
            'funds' => StatsService::getFundsStats($company),
            'loans' => StatsService::getLoansStats($company),
            'marketing' => StatsService::getMarketingStats($company),
            'production' => StatsService::getProductionStats($company),
            'sales' => StatsService::getSalesStats($company),
            'inventory' => StatsService::getInventoryStats($company),
            'employees' => StatsService::getEmployeeStats($company),
            'revenue' => StatsService::getRevenueStats($company),
            'expenses' => StatsService::getExpenseStats($company)
        ];
        
        return response()->json([
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }
}
