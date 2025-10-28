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

        $machines = Machine::with(['outputs','outputs.product', 'employeeProfile'])->latest();

        // Apply search filter
        if ($search) {
            $machines->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('name', 'like', '%' . $search . '%')
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

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'machines' => $machines->items(),
                'pagination' => IndexService::handlePagination($machines)
            ]);
        }

        return inertia('Admin/Machines/Index', [
            'machines' => $machines->items(),
            'pagination' => IndexService::handlePagination($machines)
        ]);
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
            'outputs',
            'outputs.product',
            'employeeProfile'
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
            'outputs',
            'outputs.product',
            'employeeProfile'
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

        // Handle production line outputs
        if (isset($validated['outputs']) && is_array($validated['outputs'])) {
            // Detach existing production line outputs
            $machine->products()->detach();
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