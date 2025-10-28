<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the company dashboard with comprehensive stats
     */
    public function index(Request $request)
    {
        $company = $request->company;
        $stats = StatsService::getCompanyStats($company);
        
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'stats' => $stats
            ]);
        }

        return inertia('Company/Dashboard/Index', [
            'stats' => $stats
        ]);
    }
}