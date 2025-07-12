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
        $energyMin = IndexService::checkIfNumber($request->query('energy_min'));
        $energyMax = IndexService::checkIfNumber($request->query('energy_max'));
        $speedMin = IndexService::checkIfNumber($request->query('speed_min'));
        $speedMax = IndexService::checkIfNumber($request->query('speed_max'));
        $qualityMin = IndexService::checkIfNumber($request->query('quality_min'));
        $qualityMax = IndexService::checkIfNumber($request->query('quality_max'));
        $areaMin = IndexService::checkIfNumber($request->query('area_min'));
        $areaMax = IndexService::checkIfNumber($request->query('area_max'));
        $setupTimeMin = IndexService::checkIfNumber($request->query('setup_time_min'));
        $setupTimeMax = IndexService::checkIfNumber($request->query('setup_time_max'));
        $carbonMin = IndexService::checkIfNumber($request->query('carbon_min'));
        $carbonMax = IndexService::checkIfNumber($request->query('carbon_max'));
        $failureMin = IndexService::checkIfNumber($request->query('failure_min'));
        $failureMax = IndexService::checkIfNumber($request->query('failure_max'));
        $decayMin = IndexService::checkIfNumber($request->query('decay_min'));
        $decayMax = IndexService::checkIfNumber($request->query('decay_max'));
        $maintenanceMin = IndexService::checkIfNumber($request->query('maintenance_min'));
        $maintenanceMax = IndexService::checkIfNumber($request->query('maintenance_max'));
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
        if ($energyMin) {
            $machines->where('energy_consumption_hour', '>=', $energyMin);
        }

        if ($energyMax) {
            $machines->where('energy_consumption_hour', '<=', $energyMax);
        }

        // Apply speed range filters (using average speed)
        if ($speedMin) {
            $machines->where('avg_speed_hour', '>=', $speedMin);
        }

        if ($speedMax) {
            $machines->where('avg_speed_hour', '<=', $speedMax);
        }

        // Apply quality range filters
        if ($qualityMin) {
            $machines->where('quality_factor', '>=', $qualityMin);
        }

        if ($qualityMax) {
            $machines->where('quality_factor', '<=', $qualityMax);
        }

        // Apply area range filters
        if ($areaMin) {
            $machines->where('area_required', '>=', $areaMin);
        }

        if ($areaMax) {
            $machines->where('area_required', '<=', $areaMax);
        }

        // Apply setup time range filters
        if ($setupTimeMin) {
            $machines->where('setup_time_days', '>=', $setupTimeMin);
        }

        if ($setupTimeMax) {
            $machines->where('setup_time_days', '<=', $setupTimeMax);
        }

        // Apply carbon emissions range filters
        if ($carbonMin) {
            $machines->where('carbon_emissions_hour', '>=', $carbonMin);
        }

        if ($carbonMax) {
            $machines->where('carbon_emissions_hour', '<=', $carbonMax);
        }

        // Apply failure rate range filters
        if ($failureMin) {
            $machines->where('failure_chance_hour', '>=', $failureMin);
        }

        if ($failureMax) {
            $machines->where('failure_chance_hour', '<=', $failureMax);
        }

        // Apply reliability decay range filters
        if ($decayMin) {
            $machines->where('reliability_decay_hour', '>=', $decayMin);
        }

        if ($decayMax) {
            $machines->where('reliability_decay_hour', '<=', $decayMax);
        }

        // Apply maintenance interval range filters
        if ($maintenanceMin) {
            $machines->where('maintenance_interval_days', '>=', $maintenanceMin);
        }

        if ($maintenanceMax) {
            $machines->where('maintenance_interval_days', '<=', $maintenanceMax);
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