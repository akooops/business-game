<?php

namespace App\Http\Controllers\Admin;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with leaderboard
     */
    public function index(Request $request)
    {
        // Get leaderboard data
        $leaderboard = LeaderboardService::getLeaderboard();
        $leaderboardStats = LeaderboardService::getLeaderboardStats();
        
        // If it's an AJAX request, return JSON
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'leaderboard' => $leaderboard,
                'stats' => $leaderboardStats
            ]);
        }

        return Inertia::render('Admin/Dashboard/Index', [
            'leaderboard' => $leaderboard,
            'stats' => $leaderboardStats
        ]);
    }

    /**
     * Get leaderboard data only (for real-time updates)
     */
    public function leaderboard(Request $request)
    {
        $withWeights = $request->query('weights', true);
        $leaderboard = LeaderboardService::getLeaderboard($withWeights);
        
        return response()->json([
            'leaderboard' => $leaderboard,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get leaderboard statistics
     */
    public function stats(Request $request)
    {
        $stats = LeaderboardService::getLeaderboardStats();
        
        return response()->json([
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }
}
