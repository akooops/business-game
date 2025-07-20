<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Machines\UpdateMachineRequest;
use App\Http\Requests\Admin\Machines\StoreMachineRequest;
use App\Services\FileService;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Http\Controllers\Controller;

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

        $operationCostMin = IndexService::checkIfNumber($request->query('operation_cost_min'));
        $operationCostMax = IndexService::checkIfNumber($request->query('operation_cost_max'));

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

        $machines = Machine::with(['products', 'employeeProfiles'])->latest();

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
            $machines->where('operation_cost', '>=', $operationCostMin);
        }

        if ($operationCostMax) {
            $machines->where('operation_cost', '<=', $operationCostMax);
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
            $machines->whereHas('employeeProfiles', function($query) use ($employeeProfileFilter) {
                $query->where('employee_profile_id', $employeeProfileFilter);
            });
        }

        // Apply search filter
        if ($search) {
            $machines->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('model', 'like', '%' . $search . '%')
                      ->orWhere('manufacturer', 'like', '%' . $search . '%')
                      ->orWhereHas('employeeProfiles', function($q) use ($search) {
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

        return inertia('Admin/Machines/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return inertia('Admin/Machines/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMachineRequest $request)
    {
        $validated = $request->validated();

        // Create the machine
        $machine = Machine::create($validated);

        // Handle employee profiles relationship
        if (isset($validated['employee_profiles']) && is_array($validated['employee_profiles'])) {
            foreach ($validated['employee_profiles'] as $employeeProfile) {
                $machine->employeeProfiles()->attach($employeeProfile['employee_profile_id'], [
                    'required_count' => $employeeProfile['required_count']
                ]);
            }
        }

        // Handle production line outputs
        if (isset($validated['outputs']) && is_array($validated['outputs'])) {
            foreach ($validated['outputs'] as $output) {
                $machine->outputs()->create([
                    'product_id' => $output['product_id']
                ]);
            }
        }

        // Handle image upload
        if($request->has('file')){
            $file = FileService::upload($request->file('file'));

            //Link the file to the machine
            FileService::linkModel($file, 'machine', $machine->id, 1);
        }

        return inertia('Admin/Machines/Index', [
            'success' => 'Machine created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Machine $machine)
    {    
        $machine->load([
            'products',
            'employeeProfiles'
        ]);

        return inertia('Admin/Machines/Show', compact('machine'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Machine $machine)
    {
        $machine->load([
            'products',
            'employeeProfiles'
        ]);

        return inertia('Admin/Machines/Edit', compact('machine'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Machine $machine, UpdateMachineRequest $request)
    {
        $validated = $request->validated();

        // Update the machine
        $machine->update($validated);

        // Handle employee profiles relationship
        if (isset($validated['employee_profiles']) && is_array($validated['employee_profiles'])) {
            // Detach existing employee profiles
            $machine->employeeProfiles()->detach();

            // Attach new employee profiles
            foreach ($validated['employee_profiles'] as $employeeProfile) {
                $machine->employeeProfiles()->attach($employeeProfile['employee_profile_id'], [
                    'required_count' => $employeeProfile['required_count']
                ]);
            }
        }

        // Handle production line outputs
        if (isset($validated['outputs']) && is_array($validated['outputs'])) {
            // Detach existing production line outputs
            $machine->products()->detach();

            // Attach new production line steps
            foreach ($validated['outputs'] as $output) {
                $machine->outputs()->create([
                    'product_id' => $output['product_id']
                ]);
            }
        }

        // Handle image upload
        if($request->file('file')){
            //Delete the old file if it exists
            if($machine->image){
                FileService::delete($machine->image);
            }

            $file = FileService::upload($request->file('file'));

            //Link the file to the machine
            FileService::linkModel($file, 'machine', $machine->id, 1);
        }
    
        return inertia('Admin/Machines/Index', [
            'success' => 'Machine updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Machine $machine)
    {
        $machine->delete();

        return redirect()->route('admin.machines.index')
                        ->with('success','Machine deleted successfully');
    }
} 