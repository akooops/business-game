<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard/Index');
    }
}
