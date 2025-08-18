<?php

namespace App\Http\Controllers\Company;

use App\Services\SalesService;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Sales\FixProductSalePriceRequest;
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
        $company = $request->company;
        $products = $company->companyProducts()->with(['product', 'product.recipes.material'])->latest();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'companyProducts' => $products->get(),
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
