<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Products\UpdateProductRequest;
use App\Http\Requests\Admin\Products\StoreProductRequest;
use App\Services\FileService;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
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
        $typeFilter = IndexService::checkIfSearchEmpty($request->query('type'));
        $hasExpirationFilter = IndexService::checkIfBoolean($request->query('has_expiration'));
        $elasticityMin = IndexService::checkIfNumber($request->query('elasticity_min'));
        $elasticityMax = IndexService::checkIfNumber($request->query('elasticity_max'));
        $shelfLifeMin = IndexService::checkIfNumber($request->query('shelf_life_min'));
        $shelfLifeMax = IndexService::checkIfNumber($request->query('shelf_life_max'));
        $needTechnology = IndexService::checkIfBoolean($request->query('need_technology'));
        $technologyId = IndexService::checkIfNumber($request->query('technology_id'));

        $products = Product::with('technology')->latest();

        // Apply type filter
        if ($typeFilter) {
            $products->where('type', $typeFilter);
        }

        // Apply has expiration filter
        if ($hasExpirationFilter !== null) {
            $products->where('has_expiration', $hasExpirationFilter);
        }

        // Apply elasticity range filters
        if ($elasticityMin) {
            $products->where('elasticity_coefficient', '>=', $elasticityMin);
        }

        if ($elasticityMax) {
            $products->where('elasticity_coefficient', '<=', $elasticityMax);
        }

        // Apply shelf life range filters
        if ($shelfLifeMin) {
            $products->where('shelf_life_days', '>=', $shelfLifeMin);
        }

        if ($shelfLifeMax) {
            $products->where('shelf_life_days', '<=', $shelfLifeMax);
        }

        // Apply need technology filter
        if ($needTechnology !== null) {
            $products->where('need_technology', $needTechnology);
        }

        // Apply technology filter
        if ($technologyId) {
            $products->where('technology_id', $technologyId);
        }

        // Apply search filter
        if ($search) {
            $products->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('type', 'like', '%' . $search . '%')
                      ->orWhere('measurement_unit', 'like', '%' . $search . '%');
            });
        }

        $products = $products->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'products' => $products->items(),
                'pagination' => IndexService::handlePagination($products)
            ]);
        }

        return inertia('Admin/Products/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Products/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        if($request->has('file')){
            $file = FileService::upload($request->file('file'));

            //Link the file to the product
            FileService::linkModel($file, 'product', $product->id, 1);
        }

        return inertia('Admin/Products/Index', [
            'success' => 'Product created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {    
        $product->load('technology');
        return inertia('Admin/Products/Show', compact('product'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $product->load('technology');
        return inertia('Admin/Products/Edit', compact('product'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product, UpdateProductRequest $request)
    {
        $product->update(array_merge($request->validated(), [
            'technology_id' => $request->input('need_technology') ? $request->input('technology_id') : null,
        ]));

        if($request->file('file')){            
            //Delete the old file if it exists
            if($product->image){
                FileService::delete($product->image);
            }

            $file = FileService::upload($request->file('file'));

            //Link the file to the product
            FileService::linkModel($file, 'product', $product->id, 1);
        }
    
        return inertia('Admin/Products/Index', [
            'success' => 'Product updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
                        ->with('success','Product deleted successfully');
    }
}
