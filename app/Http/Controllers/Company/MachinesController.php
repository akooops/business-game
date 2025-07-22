<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Machines\SetupMachineRequest;
use App\Http\Requests\Company\Machines\AssignEmployeeRequest;
use App\Http\Requests\Company\Machines\StartMaintenanceRequest;
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
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // Filter parameters
        $manufacturerFilter = IndexService::checkIfSearchEmpty($request->query('manufacturer'));

        $priceMin = IndexService::checkIfNumber($request->query('price_min'));
        $priceMax = IndexService::checkIfNumber($request->query('price_max'));

        $setupTimeMin = IndexService::checkIfNumber($request->query('setup_time_min'));
        $setupTimeMax = IndexService::checkIfNumber($request->query('setup_time_max'));

        $operationCostMin = IndexService::checkIfNumber($request->query('operations_cost_min'));
        $operationCostMax = IndexService::checkIfNumber($request->query('operations_cost_max'));

        $minSpeed = IndexService::checkIfNumber($request->query('min_speed'));
        $maxSpeed = IndexService::checkIfNumber($request->query('max_speed'));

        $qualityFactorMin = IndexService::checkIfNumber($request->query('quality_factor_min'));
        $qualityFactorMax = IndexService::checkIfNumber($request->query('quality_factor_max'));

        $carbonFootprintMin = IndexService::checkIfNumber($request->query('carbon_footprint_min'));
        $carbonFootprintMax = IndexService::checkIfNumber($request->query('carbon_footprint_max'));

        $reliabilityDecayDaysMin = IndexService::checkIfNumber($request->query('reliability_decay_days_min'));
        $reliabilityDecayDaysMax = IndexService::checkIfNumber($request->query('reliability_decay_days_max'));

        $productFilter = IndexService::checkIfSearchEmpty($request->query('product_id'));
        $employeeProfileFilter = IndexService::checkIfSearchEmpty($request->query('employee_profile_id'));

        $status = IndexService::checkIfSearchEmpty($request->query('status'));

        $company = $request->company;   
        $machines = $company->companyMachines()->with(['machine', 'machine.products', 'machine.employeeProfile', 'employee'])->latest();

        // Apply manufacturer filter
        if ($manufacturerFilter) {
            $machines->where('manufacturer', $manufacturerFilter);
        }

        // Apply price range filters
        if ($priceMin) {
            $machines->where('cost_to_acquire', '>=', $priceMin);
        }

        if ($priceMax) {
            $machines->where('cost_to_acquire', '<=', $priceMax);
        }

        // Apply energy consumption range filters
        if ($operationCostMin) {
            $machines->where('operations_cost', '>=', $operationCostMin);
        }

        if ($operationCostMax) {
            $machines->where('operations_cost', '<=', $operationCostMax);
        }

        // Apply speed range filters (using average speed)
        if ($minSpeed) {
            $machines->where('avg_speed', '>=', $minSpeed);
        }

        if ($maxSpeed) {
            $machines->where('avg_speed', '<=', $maxSpeed);
        }

        // Apply quality range filters
        if ($qualityFactorMin) {
            $machines->where('quality_factor', '>=', $qualityFactorMin);
        }

        if ($qualityFactorMax) {
            $machines->where('quality_factor', '<=', $qualityFactorMax);
        }

        // Apply area range filters
        if ($carbonFootprintMin) {
            $machines->where('carbon_footprint', '>=', $carbonFootprintMin);
        }

        if ($carbonFootprintMax) {
            $machines->where('carbon_footprint', '<=', $carbonFootprintMax);
        }

        // Apply setup time range filters
        if ($setupTimeMin) {
            $machines->where('setup_time_days', '>=', $setupTimeMin);
        }

        if ($setupTimeMax) {
            $machines->where('setup_time_days', '<=', $setupTimeMax);
        }

        // Apply carbon emissions range filters
        if ($reliabilityDecayDaysMin) {
            $machines->where('reliability_decay_days', '>=', $reliabilityDecayDaysMin);
        }

        if ($reliabilityDecayDaysMax) {
            $machines->where('reliability_decay_days', '<=', $reliabilityDecayDaysMax);
        }

        // Apply product filter
        if ($productFilter) {
            $machines->whereHas('outputs', function($query) use ($productFilter) {
                $query->where('product_id', $productFilter);
            });
        }

        // Apply employee profile filter
        if ($employeeProfileFilter) {
            $machines->whereHas('employeeProfile', function($query) use ($employeeProfileFilter) {
                $query->where('employee_profile_id', $employeeProfileFilter);
            });
        }

        if($status){
            $machines->where('status', $status);
        }

        // Apply search filter
        if ($search) {
            $machines->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('model', 'like', '%' . $search . '%')
                      ->orWhere('manufacturer', 'like', '%' . $search . '%')
                      ->orWhereHas('employeeProfile', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('outputs.product', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $machines = $machines->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'machines' => $machines->items(),
                'pagination' => IndexService::handlePagination($machines)
            ]);
        }

        return inertia('Company/Machines/Index');
    }

    public function setupPage(Request $request)
    {
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        $machines = Machine::with(['products', 'employeeProfile'])->latest();

        // Apply search filter
        if ($search) {
            $machines->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('model', 'like', '%' . $search . '%')
                      ->orWhere('manufacturer', 'like', '%' . $search . '%')
                      ->orWhereHas('employeeProfile', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('outputs.product', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $machines = $machines->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'machines' => $machines->items(),
                'pagination' => IndexService::handlePagination($machines)
            ]);
        }

        return inertia('Company/Machines/SetupPage');
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
} 