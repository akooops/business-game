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
        $status = IndexService::checkIfSearchEmpty($request->query('status'));

        $company = $request->company;
        $employees = $company->employees()->with(['employeeProfile', 'companyMachine', 'companyMachine.machine'])->orderBy('status')->latest();
        
        if($status){
            $employees->where('status', $status);
        }else{
            $employees->whereNotIn('status', [Employee::STATUS_APPLIED]);
        }

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'employees' => $employees->get(),
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
