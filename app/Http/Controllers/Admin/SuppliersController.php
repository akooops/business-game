<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Suppliers\UpdateSupplierRequest;
use App\Http\Requests\Admin\Suppliers\StoreSupplierRequest;
use App\Services\FileService;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Country;
use App\Models\Wilaya;
use App\Models\Product;

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

        // Filter parameters
        $isInternational = IndexService::checkIfBoolean($request->query('is_international'));
        $countryId = IndexService::checkIfNumber($request->query('country_id'));
        $wilayaId = IndexService::checkIfNumber($request->query('wilaya_id'));
        $minShippingCost = IndexService::checkIfNumber($request->query('min_shipping_cost'));
        $maxShippingCost = IndexService::checkIfNumber($request->query('max_shipping_cost'));
        $minShippingTime = IndexService::checkIfNumber($request->query('min_shipping_time'));
        $maxShippingTime = IndexService::checkIfNumber($request->query('max_shipping_time'));
        $carbonFootprintMin = IndexService::checkIfNumber($request->query('carbon_footprint_min'));
        $carbonFootprintMax = IndexService::checkIfNumber($request->query('carbon_footprint_max'));

        $suppliers = Supplier::with(['country', 'wilaya', 'products'])->latest();

        // Apply international/local filter
        if ($isInternational !== null) {
            $suppliers->where('is_international', $isInternational);
        }

        // Apply country filter
        if ($countryId) {
            $suppliers->where('country_id', $countryId);
        }

        // Apply wilaya filter
        if ($wilayaId) {
            $suppliers->where('wilaya_id', $wilayaId);
        }

        // Apply shipping cost range filters
        if ($minShippingCost) {
            $suppliers->where('avg_shipping_cost', '>=', $minShippingCost);
        }

        if ($maxShippingCost) {
            $suppliers->where('avg_shipping_cost', '<=', $maxShippingCost);
        }

        // Apply shipping time range filters
        if ($minShippingTime) {
            $suppliers->where('avg_shipping_time_days', '>=', $minShippingTime);
        }

        if ($maxShippingTime) {
            $suppliers->where('avg_shipping_time_days', '<=', $maxShippingTime);
        }

        // Apply carbon footprint range filters
        if ($carbonFootprintMin) {
            $suppliers->where('carbon_footprint', '>=', $carbonFootprintMin);
        }

        if ($carbonFootprintMax) {
            $suppliers->where('carbon_footprint', '<=', $carbonFootprintMax);
        }

        // Apply search filter
        if ($search) {
            $suppliers->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
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
                'avg_sale_price' => $productData['avg_sale_price'],
                'max_sale_price' => $productData['max_sale_price'],
                'real_sale_price' => $productData['avg_sale_price'], // Default to avg
                'minimum_order_qty' => $productData['minimum_order_qty'],
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
        $supplier->load(['country', 'wilaya', 'products']);
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
        $supplier->load(['country', 'wilaya', 'products']);

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
                'avg_sale_price' => $productData['avg_sale_price'],
                'max_sale_price' => $productData['max_sale_price'],
                'real_sale_price' => $productData['avg_sale_price'], // Default to avg
                'minimum_order_qty' => $productData['minimum_order_qty'],
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
