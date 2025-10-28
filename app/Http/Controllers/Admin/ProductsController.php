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

        $products = Product::with('technology')->latest();

        // Apply search filter
        if ($search) {
            $products->where(function($query) use ($search) {
                // Only search by ID if search term is numeric
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $products = $products->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'products' => $products->items(),
                'pagination' => IndexService::handlePagination($products)
            ]);
        }

        return inertia('Admin/Products/Index', [
            'products' => $products->items(),
            'pagination' => IndexService::handlePagination($products)
        ]);
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
        $product = Product::create(array_merge($request->validated(), [
            'technology_id' => $request->input('needs_technology') ? $request->input('technology_id') : null,
        ]));

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
            'technology_id' => $request->input('needs_technology') ? $request->input('technology_id') : null,
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
