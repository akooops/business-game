<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\EmployeeProfile;
use App\Http\Controllers\Controller;
use App\Services\HrService;

class EmployeeProfilesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employeeProfiles = EmployeeProfile::latest();

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'employeeProfiles' => $employeeProfiles->get(),
            ]);
        }

        return inertia('Company/EmployeeProfiles/Index', [
            'employeeProfiles' => $employeeProfiles->get()
        ]);
    }

    public function findEmployees(Request $request, EmployeeProfile $employeeProfile){
        $employees = HrService::generateEmployees($request->company, $employeeProfile);

        return response()->json([
            'employees' => $employees,
        ]);
    }
} 