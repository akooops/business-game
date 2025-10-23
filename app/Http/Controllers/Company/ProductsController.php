<?php

namespace App\Http\Controllers\Company;

use App\Services\SalesService;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Sales\FixProductSalePriceRequest;
use App\Models\Product;
use App\Services\IndexService;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = IndexService::checkIfSearchEmpty($request->query('search'));
        $forProcurement = $request->query('for_procurement', false);

        $company = $request->company;
        $products = $company->companyProducts()
            ->with(['product', 'product.recipes.material']);

        // If this is for procurement, only show raw materials and components
        if ($forProcurement) {
            $products->whereHas('product', function($query) {
                $query->whereIn('type', ['raw_material', 'component']);
            });
        }

        $products = $products->latest();

        if ($search) {
            $products->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('product_id', $search)
                      ->orWhereHas('product', function($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%')
                              ->orWhere('type', 'like', '%' . $search . '%');
                      });
            });
        }

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            $perPage = $request->query('perPage', 10);
            $page = $request->query('page', 1);
            
            $products = $products->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'companyProducts' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ]);
        }

        return inertia('Company/Products/Index');
    }

    public function fixProductSalePrice(FixProductSalePriceRequest $request, Product $product)
    {
        SalesService::fixProductSalePrice($request->company, $product, $request->sale_price);

        return response()->json([
            'status' => 'success',
            'message' => 'Product sale price fixed successfully!'
        ]);
    }
}
