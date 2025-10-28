<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Machines\SetupMachineRequest;
use App\Http\Requests\Company\Machines\AssignEmployeeRequest;
use App\Http\Requests\Company\Machines\StartMaintenanceRequest;
use App\Http\Requests\Company\Machines\SellMachineRequest;
use App\Services\FileService;
use App\Services\IndexService;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\CompanyMachine;
use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;

class MachinesController extends Controller
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

        $company = $request->company;   
        $machines = $company->companyMachines()->whereNot('status', CompanyMachine::STATUS_SOLD)->with(['machine', 'machine.outputs', 'machine.outputs.product', 'machine.employeeProfile', 'employee', 'ongoingProductionOrder', 'ongoingProductionOrder.product'])->latest();

        $machines = $machines->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'machines' => $machines->items(),
                'pagination' => IndexService::handlePagination($machines)
            ]);
        }

        return inertia('Company/Machines/Index', [
            'machines' => $machines->items(),
            'pagination' => IndexService::handlePagination($machines)
        ]);
    }

    public function setupPage(Request $request)
    {
        $machines = Machine::with(['outputs', 'outputs.product', 'employeeProfile'])->latest();

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'machines' => $machines->get(),
            ]);
        }

        return inertia('Company/Machines/SetupPage', [
            'machines' => $machines->get()
        ]);
    }

    public function setup(SetupMachineRequest $request, Machine $machine)
    {
        $company = $request->company;

        ProductionService::setupMachine($company, $machine);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Machine setup successfully!'
        ]);
    }

    public function assignEmployee(AssignEmployeeRequest $request, CompanyMachine $companyMachine)
    {
        $employee = Employee::find($request->employee_id);

        ProductionService::assignEmployee($companyMachine, $employee);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Employee assigned successfully!'
        ]);
    }

    public function unassignEmployee(CompanyMachine $companyMachine)
    {
        ProductionService::unassignEmployee($companyMachine);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Employee unassigned successfully!'
        ]);
    }

    public function startMaintenance(StartMaintenanceRequest $request, CompanyMachine $companyMachine)
    {
        MaintenanceService::startMaintenance($companyMachine);

        return response()->json([
            'status' => 'success',
            'message' => 'Maintenance started successfully!'
        ]);
    }

    public function sell(SellMachineRequest $request, CompanyMachine $companyMachine)
    {
        $company = $request->company;
        ProductionService::sellMachine($companyMachine);

        return response()->json([
            'status' => 'success',
            'message' => 'Machine sold successfully!'
        ]);
    }
} 