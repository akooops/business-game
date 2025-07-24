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
        return response()->json([
            'status' => 'success',
            'timestamp' => SettingsService::getCurrentTimestamp()->format('Y-m-d H:i'),
            'currentGameWeek' => SettingsService::getCurrentGameWeek(),
        ]);
    }
} 