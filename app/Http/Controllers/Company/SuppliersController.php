<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SuppliersController extends Controller
{
    public function index(Request $request)
    {   
        $suppliers = Supplier::with(['country', 'wilaya', 'supplierProducts', 'supplierProducts.product'])->latest();

        $product_id = IndexService::checkIfNumber($request->query('product_id'));

        if($product_id){
            $suppliers->whereHas('supplierProducts', function($query) use ($product_id){
                $query->where('product_id', $product_id);
            });

            // Get suppliers with only the specific product
            $suppliers = $suppliers->get();
            
            // For each supplier, keep only the specific product
            foreach($suppliers as $supplier) {
                $supplierProduct = $supplier->supplierProducts->where('product_id', $product_id)->first();
                
                // Add selected_product data for frontend compatibility
                if($supplierProduct) {
                    $supplier->selected_product = $supplierProduct;
                    unset($supplier->supplierProducts);
                }
            }
        } else {
            $suppliers = $suppliers->get();
        }

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'suppliers' => $suppliers
            ]);
        }

        return inertia('Company/Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }
}
