<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        return Inertia::render('Company/Dashboard/Index', [
            'user' => $user,
            'company' => $company,
        ]);
    }
}
