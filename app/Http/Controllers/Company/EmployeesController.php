<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Employees\RecruitEmployeeRequest;
use App\Http\Requests\Company\Employees\PromoteEmployeeRequest;
use App\Http\Requests\Company\Employees\FireEmployeeRequest;
use App\Models\Employee;
use App\Services\HrService;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // Filter parameters
        $employeeProfileId = IndexService::checkIfNumber($request->query('employee_profile_id'));
        $status = IndexService::checkIfSearchEmpty($request->query('status'));

        $current_mood_min = IndexService::checkIfEmpty($request->query('current_mood_min'));
        $current_mood_max = IndexService::checkIfEmpty($request->query('current_mood_max'));

        $mood_decay_rate_days_min = IndexService::checkIfEmpty($request->query('mood_decay_rate_days_min'));
        $mood_decay_rate_days_max = IndexService::checkIfEmpty($request->query('mood_decay_rate_days_max'));
        
        $efficiency_factor_min = IndexService::checkIfEmpty($request->query('efficiency_factor_min'));
        $efficiency_factor_max = IndexService::checkIfEmpty($request->query('efficiency_factor_max'));

        $company = $request->company;

        $employees = $company->employees()->with(['employeeProfile', 'companyMachine', 'companyMachine.machine'])->latest();

        if($employeeProfileId){
            $employees->where('employee_profile_id', $employeeProfileId);
        }


        if($status){
            $employees->where('status', $status);
        }else{
            $employees->whereNot('status', Employee::STATUS_APPLIED);
        }

        if($current_mood_min){
            $employees->where('current_mood', '>=', $current_mood_min);
        }

        if($current_mood_max){
            $employees->where('current_mood', '<=', $current_mood_max);
        }

        if($mood_decay_rate_days_min){
            $employees->where('mood_decay_rate_days', '>=', $mood_decay_rate_days_min);
        }

        if($mood_decay_rate_days_max){
            $employees->where('mood_decay_rate_days', '<=', $mood_decay_rate_days_max);
        }

        if($efficiency_factor_min){
            $employees->where('efficiency_factor', '>=', $efficiency_factor_min);    
        }

        if($efficiency_factor_max){
            $employees->where('efficiency_factor', '<=', $efficiency_factor_max);
        }

        if ($search) {
            $employees->where(function($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('employeeProfile', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $employees = $employees->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'employees' => $employees->items(),
                'pagination' => IndexService::handlePagination($employees),
            ]);
        }

        return inertia('Company/Employees/Index');
    }

    public function recruitPage(Request $request){
        return inertia('Company/Employees/RecruitPage');
    }

    public function recruit(RecruitEmployeeRequest $request, Employee $employee){
        HrService::recruitEmployee($employee);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee recruited successfully!'
        ]);
        
    }

    public function promote(PromoteEmployeeRequest $request, Employee $employee){
        HrService::promoteEmployee($employee, $request->new_salary);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee promoted successfully!'
        ]);
    }

    public function fire(FireEmployeeRequest $request, Employee $employee){
        HrService::fireEmployee($employee);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee fired successfully!'
        ]);
    }
}
