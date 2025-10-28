<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductDemand\StoreProductDemandRequest;
use App\Http\Requests\Admin\ProductDemand\UpdateProductDemandRequest;
use App\Models\Product;
use App\Models\ProductDemand;
use App\Services\IndexService;
use Illuminate\Http\Request;

class ProductDemandController extends Controller
{
    /**
     * Display the product demand management page
     */
    public function index(Request $request)
    {
        $productDemands = [];
        $product = null;
        
        if($request->has('product_id')){
            $product = Product::findOrFail($request->product_id);

            $productDemands = $product->demands()->latest()->get();

            if ($request->expectsJson() && !$request->header('X-Inertia')) {
                return response()->json([
                    'productDemands' => $productDemands
                ]);
            }
        }

        return inertia('Admin/ProductDemand/Index', [
            'productDemands' => $productDemands,
            'product' => $product
        ]);
    }
    
    /**
     * Store a new demand record
     */
    public function store(StoreProductDemandRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        $validated = $request->validated();
        
        // Get the last gameweek for this product and add 1
        $lastGameweek = $product->demands()
            ->max('gameweek') ?? 0;
        $nextGameweek = $lastGameweek + 1;

        $demand = ProductDemand::create(array_merge(
            $validated,
            [
                'gameweek' => $nextGameweek
            ]
        ));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Demand data created successfully!'
        ]);
    }
    
    /**
     * Update an existing demand record
     */
    public function update(UpdateProductDemandRequest $request, ProductDemand $productDemand)
    {
        $validated = $request->validated();
        
        $productDemand->update($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Demand data updated successfully!'
        ]);
    }
    
    /**
     * Delete a demand record
     */
    public function destroy(ProductDemand $productDemand)
    {
        $deletedGameweek = $productDemand->gameweek;
        
        // Delete the record
        $productDemand->delete();
        
        // Update all gameweeks >= deleted gameweek to be -1 (shift down)
        $productDemand->product->demands()
            ->where('gameweek', '>=', $deletedGameweek)
            ->decrement('gameweek');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Demand data deleted successfully!'
        ]);
    }
} 