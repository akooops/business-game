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
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        $employeeProfiles = EmployeeProfile::latest();

        // Apply search filter
        if ($search) {
            $employeeProfiles->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $employeeProfiles = $employeeProfiles->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'employeeProfiles' => $employeeProfiles->items(),
                'pagination' => IndexService::handlePagination($employeeProfiles)
            ]);
        }

        return inertia('Company/EmployeeProfiles/Index');
    }

    public function findEmployees(Request $request, EmployeeProfile $employeeProfile){
        $employees = HrService::generateEmployees($request->company, $employeeProfile);

        return response()->json([
            'employees' => $employees,
        ]);
    }
} 