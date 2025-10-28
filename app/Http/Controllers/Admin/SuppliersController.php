<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Suppliers\UpdateSupplierRequest;
use App\Http\Requests\Admin\Suppliers\StoreSupplierRequest;
use App\Services\FileService;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Services\CalculationsService;

class SuppliersController extends Controller
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

        $suppliers = Supplier::with(['country', 'wilaya', 'supplierProducts', 'supplierProducts.product'])->latest();

        // Apply search filter
        if ($search) {
            $suppliers->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhereHas('country', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('wilaya', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('products', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
            });
        }

        $suppliers = $suppliers->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'suppliers' => $suppliers->items(),
                'pagination' => IndexService::handlePagination($suppliers)
            ]);
        }

        return inertia('Admin/Suppliers/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Suppliers/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSupplierRequest $request)
    {
        $supplierData = $request->validated();
        $products = $supplierData['products'];

        // Set location based on type
        if ($supplierData['is_international']) {
            $supplierData['wilaya_id'] = null;
        } else {
            $supplierData['country_id'] = null;
        }

        $supplierData['real_shipping_cost'] = CalculationsService::calcaulteRandomBetweenMinMax($supplierData['min_shipping_cost'], $supplierData['max_shipping_cost']);
        $supplierData['real_shipping_time_days'] = CalculationsService::calcaulteRandomBetweenMinMax($supplierData['min_shipping_time_days'], $supplierData['max_shipping_time_days']);

        $supplier = Supplier::create($supplierData);

        // Handle file upload
        if($request->has('file')){
            $file = FileService::upload($request->file('file'));

            //Link the file to the supplier
            FileService::linkModel($file, 'supplier', $supplier->id, 1);
        }

        // Create supplier products relationships
        foreach ($products as $productData) {
            $supplier->supplierProducts()->create([
                'product_id' => $productData['product_id'],
                'min_sale_price' => $productData['min_sale_price'],
                'max_sale_price' => $productData['max_sale_price'],
                'real_sale_price' => CalculationsService::calcaulteRandomBetweenMinMax($productData['min_sale_price'], $productData['max_sale_price']),
            ]);
        }

        return inertia('Admin/Suppliers/Index', [
            'success' => 'Supplier created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {    
        $supplier->load(['country', 'wilaya', 'supplierProducts', 'supplierProducts.product']);
        return inertia('Admin/Suppliers/Show', compact('supplier'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $supplier->load(['country', 'wilaya', 'supplierProducts', 'supplierProducts.product']);

        return inertia('Admin/Suppliers/Edit', compact('supplier'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Supplier $supplier, UpdateSupplierRequest $request)
    {
        $supplierData = $request->validated();
        $products = $supplierData['products'];

        // Set location based on type
        if ($supplierData['is_international']) {
            $supplierData['wilaya_id'] = null;
        } else {
            $supplierData['country_id'] = null;
        }

        $supplierData['real_shipping_cost'] = CalculationsService::calcaulteRandomBetweenMinMax($supplierData['min_shipping_cost'], $supplierData['max_shipping_cost']);
        $supplierData['real_shipping_time_days'] = CalculationsService::calcaulteRandomBetweenMinMax($supplierData['min_shipping_time_days'], $supplierData['max_shipping_time_days']);

        $supplier->update($supplierData);

        // Handle file upload
        if($request->file('file')){            
            // Delete the old file if it exists
            if($supplier->image){
                FileService::delete($supplier->image);
            }

            $file = FileService::upload($request->file('file'));
            FileService::linkModel($file, 'supplier', $supplier->id, 1);
        }

        // Update supplier products relationships
        // Delete existing relationships
        $supplier->supplierProducts()->delete();

        // Create new relationships
        foreach ($products as $productData) {
            $supplier->supplierProducts()->create([
                'product_id' => $productData['product_id'],
                'min_sale_price' => $productData['min_sale_price'],
                'max_sale_price' => $productData['max_sale_price'],
                'real_sale_price' => CalculationsService::calcaulteRandomBetweenMinMax($productData['min_sale_price'], $productData['max_sale_price']),
            ]);
        }
    
        return inertia('Admin/Suppliers/Index', [
            'success' => 'Supplier updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
                        ->with('success','Supplier deleted successfully');
    }
}
