<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Services\ProcurementService;
use App\Http\Requests\Company\Suppliers\PurchaseProductRequest;

class SuppliersController extends Controller
{
    public function index(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // Filter parameters
        $isInternational = IndexService::checkIfBoolean($request->query('is_international'));
        $countryId = IndexService::checkIfNumber($request->query('country_id'));
        $wilayaId = IndexService::checkIfNumber($request->query('wilaya_id'));
        $productId = IndexService::checkIfNumber($request->query('product_id'));

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

        // Apply product filter
        if ($productId) {
            $suppliers->whereHas('supplierProducts', function($q) use ($productId) {
                $q->where('product_id', $productId);
            });
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
            $suppliersData = $suppliers->items();
            
            // If productId is provided, add the specific product data to each supplier
            if ($productId) {
                foreach ($suppliersData as $supplier) {
                    $supplierProduct = $supplier->products->where('id', $productId)->first();
                    if ($supplierProduct) {
                        $supplier->selected_product = $supplierProduct;
                    }
                }
            }
            
            return response()->json([
                'suppliers' => $suppliersData,
                'pagination' => IndexService::handlePagination($suppliers),
            ]);
        }

        return inertia('Company/Suppliers/Index');
    }
}
