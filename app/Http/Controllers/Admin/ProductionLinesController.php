<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProductionLines\UpdateProductionLineRequest;
use App\Http\Requests\Admin\ProductionLines\StoreProductionLineRequest;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\ProductionLine;
use App\Models\Product;
use App\Models\ProductionLineStep;
use App\Http\Controllers\Controller;

class ProductionLinesController extends Controller
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
        $areaMin = IndexService::checkIfNumber($request->query('area_min'));
        $areaMax = IndexService::checkIfNumber($request->query('area_max'));
        $productFilter = IndexService::checkIfSearchEmpty($request->query('product_id'));
        $stepsMin = IndexService::checkIfNumber($request->query('steps_min'));
        $stepsMax = IndexService::checkIfNumber($request->query('steps_max'));

        $productionLines = ProductionLine::with(['products', 'steps'])->latest();

        // Apply area range filters
        if ($areaMin) {
            $productionLines->where('area_required', '>=', $areaMin);
        }

        if ($areaMax) {
            $productionLines->where('area_required', '<=', $areaMax);
        }

        // Apply product filter
        if ($productFilter) {
            $productionLines->whereHas('outputs', function($query) use ($productFilter) {
                $query->where('product_id', $productFilter);
            });
        }

        // Apply steps filter
        if ($stepsMin) {
            $productionLines->has('steps', '>=', $stepsMin);
        }

        if ($stepsMax) {
            $productionLines->has('steps', '<=', $stepsMax);
        }

        // Apply search filter
        if ($search) {
            $productionLines->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhereHas('outputs.product', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
            });
        }

        $productionLines = $productionLines->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'productionLines' => $productionLines->items(),
                'pagination' => IndexService::handlePagination($productionLines)
            ]);
        }

        return inertia('Admin/ProductionLines/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return inertia('Admin/ProductionLines/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductionLineRequest $request)
    {
        $validated = $request->validated();

        // Create the production line
        $productionLine = ProductionLine::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'area_required' => $validated['area_required'],
        ]);

        // Handle production line outputs
        if (isset($validated['outputs']) && is_array($validated['outputs'])) {
            foreach ($validated['outputs'] as $output) {
                $productionLine->outputs()->create([
                    'product_id' => $output['product_id']
                ]);
            }
        }

        // Handle production line steps
        if (isset($validated['steps']) && is_array($validated['steps'])) {
            foreach ($validated['steps'] as $key => $step) {
                $productionLine->steps()->create([
                    'name' => $step['name'],
                    'description' => $step['description'] ?? null,
                    'step' => $key + 1
                ]);
            }
        }

        return inertia('Admin/ProductionLines/Index', [
            'success' => 'Production line created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ProductionLine $productionLine)
    {    
        $productionLine->load([
            'products',
            'steps'
        ]);

        return inertia('Admin/ProductionLines/Show', compact('productionLine'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductionLine $productionLine)
    {
        $productionLine->load([
            'products',
            'steps'
        ]);

        return inertia('Admin/ProductionLines/Edit', compact('productionLine'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductionLine $productionLine, UpdateProductionLineRequest $request)
    {
        $validated = $request->validated();

        // Update the production line
        $productionLine->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'area_required' => $validated['area_required'],
        ]);

        // Handle production line outputs
        if (isset($validated['outputs']) && is_array($validated['outputs'])) {
            // Delete existing outputs
            $productionLine->outputs()->delete();

            // Create new outputs
            foreach ($validated['outputs'] as $output) {
                $productionLine->outputs()->create([
                    'product_id' => $output['product_id']
                ]);
            }
        }

        // Handle production line steps
        if (isset($validated['steps']) && is_array($validated['steps'])) {
            // Delete existing steps
            $productionLine->steps()->delete();

            // Create new steps
            foreach ($validated['steps'] as $key => $step) {
                $productionLine->steps()->create([
                    'name' => $step['name'],
                    'description' => $step['description'] ?? null,
                    'step' => $key + 1
                ]);
            }
        }
    
        return inertia('Admin/ProductionLines/Index', [
            'success' => 'Production line updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductionLine $productionLine)
    {
        $productionLine->delete();

        return redirect()->route('admin.production-lines.index')
                        ->with('success','Production line deleted successfully');
    }
}
