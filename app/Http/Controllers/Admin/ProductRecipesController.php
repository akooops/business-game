<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRecipes\StoreProductRecipeRequest;
use App\Http\Requests\Admin\ProductRecipes\UpdateProductRecipeRequest;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Services\IndexService;
use Illuminate\Http\Request;

class ProductRecipesController extends Controller
{
    /**
     * Display the product recipe management page
     */
    public function index(Request $request)
    {
        // Filter parameters
        $productId = IndexService::checkIfSearchEmpty($request->query('product_id'));
        
        $productRecipes = ProductRecipe::with(['product', 'material'])->latest();

        // Apply product filter
        if ($productId) {
            $productRecipes->where('product_id', $productId);
        }

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'productRecipes' => $productRecipes->get()
            ]);
        }

        return inertia('Admin/ProductRecipes/Index', [
            'productRecipes' => $productRecipes->get()
        ]);
    }
    
    /**
     * Store a new recipe record
     */
    public function store(StoreProductRecipeRequest $request)
    {
        $validated = $request->validated();
        
        $recipe = ProductRecipe::create($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Material added to recipe successfully!'
        ]);
    }
    
    /**
     * Update an existing recipe record
     */
    public function update(UpdateProductRecipeRequest $request, ProductRecipe $productRecipe)
    {
        $validated = $request->validated();
        
        $productRecipe->update($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Recipe updated successfully!'
        ]);
    }
    
    /**
     * Delete a recipe record
     */
    public function destroy(ProductRecipe $productRecipe)
    {
        $productRecipe->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Material removed from recipe successfully!'
        ]);
    }
} 