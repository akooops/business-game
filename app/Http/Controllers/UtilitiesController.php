<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class UtilitiesController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $company = auth()->user()->company;

        return response()->json([
            'status' => 'success',
            'timestamp' => SettingsService::getCurrentTimestamp()->format('Y-m-d H:i'),
            'currentGameWeek' => SettingsService::getCurrentGameWeek(),
            'funds' => ($company) ? $company->funds : 0,
            'unpaidLoans' => ($company) ? $company->unpaid_loans : 0,
            'researchLevel' => ($company) ? $company->research_level : 0,
            'carbonFootprint' => ($company) ? $company->carbon_footprint : 0,
        ]);
    }
} 