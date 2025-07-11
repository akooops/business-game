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

        // Filter parameters
        $difficultyFilter = IndexService::checkIfSearchEmpty($request->query('recruitment_difficulty'));
        $salaryMin = IndexService::checkIfNumber($request->query('salary_min'));
        $salaryMax = IndexService::checkIfNumber($request->query('salary_max'));
        $recruitmentCostMin = IndexService::checkIfNumber($request->query('recruitment_cost_min'));
        $recruitmentCostMax = IndexService::checkIfNumber($request->query('recruitment_cost_max'));
        $trainingCostMin = IndexService::checkIfNumber($request->query('training_cost_min'));
        $trainingCostMax = IndexService::checkIfNumber($request->query('training_cost_max'));
        $trainingDurationMin = IndexService::checkIfNumber($request->query('training_duration_min'));
        $trainingDurationMax = IndexService::checkIfNumber($request->query('training_duration_max'));

        $employeeProfiles = EmployeeProfile::latest();

        // Apply recruitment difficulty filter
        if ($difficultyFilter) {
            $employeeProfiles->where('recruitment_difficulty', $difficultyFilter);
        }

        // Apply salary range filters (using average salary for filtering)
        if ($salaryMin) {
            $employeeProfiles->where('monthly_avg_salary', '>=', $salaryMin);
        }

        if ($salaryMax) {
            $employeeProfiles->where('monthly_avg_salary', '<=', $salaryMax);
        }

        // Apply recruitment cost range filters
        if ($recruitmentCostMin) {
            $employeeProfiles->where('recruitment_cost_per_employee', '>=', $recruitmentCostMin);
        }

        if ($recruitmentCostMax) {
            $employeeProfiles->where('recruitment_cost_per_employee', '<=', $recruitmentCostMax);
        }

        // Apply training cost range filters
        if ($trainingCostMin) {
            $employeeProfiles->where('training_cost_per_employee', '>=', $trainingCostMin);
        }

        if ($trainingCostMax) {
            $employeeProfiles->where('training_cost_per_employee', '<=', $trainingCostMax);
        }

        // Apply training duration range filters
        if ($trainingDurationMin) {
            $employeeProfiles->where('training_duration_days', '>=', $trainingDurationMin);
        }

        if ($trainingDurationMax) {
            $employeeProfiles->where('training_duration_days', '<=', $trainingDurationMax);
        }

        // Apply search filter
        if ($search) {
            $employeeProfiles->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('skills', 'like', '%' . $search . '%')
                      ->orWhere('recruitment_difficulty', 'like', '%' . $search . '%');
            });
        }

        $employeeProfiles = $employeeProfiles->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'employeeProfiles' => $employeeProfiles->items(),
                'pagination' => IndexService::handlePagination($employeeProfiles)
            ]);
        }

        return inertia('Admin/EmployeeProfiles/Index');
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
        $validated = $request->validated();

        // Create the employee profile
        $employeeProfile = EmployeeProfile::create($validated);

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
        $validated = $request->validated();

        // Update the employee profile
        $employeeProfile->update($validated);
    
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