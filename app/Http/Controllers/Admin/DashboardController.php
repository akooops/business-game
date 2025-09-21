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
        $companies = LeaderboardService::getLeaderboard();
    
        // If it's an AJAX request, return JSON
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'companies' => $companies,
            ]);
        }

        return Inertia::render('Admin/Dashboard/Index');
    }
}
