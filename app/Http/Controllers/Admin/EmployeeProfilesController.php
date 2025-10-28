<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\EmployeeProfiles\UpdateEmployeeProfileRequest;
use App\Http\Requests\Admin\EmployeeProfiles\StoreEmployeeProfileRequest;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\EmployeeProfile;
use App\Http\Controllers\Controller;

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

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'employeeProfiles' => $employeeProfiles->items(),
                'pagination' => IndexService::handlePagination($employeeProfiles)
            ]);
        }

        return inertia('Admin/EmployeeProfiles/Index', [
            'employeeProfiles' => $employeeProfiles->items(),
            'pagination' => IndexService::handlePagination($employeeProfiles)
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return inertia('Admin/EmployeeProfiles/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeProfileRequest $request)
    {
        // Create the employee profile
        $employeeProfile = EmployeeProfile::create($request->validated());

        return inertia('Admin/EmployeeProfiles/Index', [
            'success' => 'Employee profile created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeProfile $employeeProfile)
    {    
        return inertia('Admin/EmployeeProfiles/Show', compact('employeeProfile'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeProfile $employeeProfile)
    {
        return inertia('Admin/EmployeeProfiles/Edit', compact('employeeProfile'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeProfile $employeeProfile, UpdateEmployeeProfileRequest $request)
    {
        // Update the employee profile
        $employeeProfile->update($request->validated());
    
        return inertia('Admin/EmployeeProfiles/Index', [
            'success' => 'Employee profile updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeProfile $employeeProfile)
    {
        $employeeProfile->delete();

        return redirect()->route('admin.employee-profiles.index')
                        ->with('success','Employee profile deleted successfully');
    }
} 