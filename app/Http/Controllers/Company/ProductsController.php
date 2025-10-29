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
        $type = $request->query('type');
        $sort = $request->query('sort');

        $company = $request->company;
        $products = $company->companyProducts()->with(['product', 'product.recipes.material']);

        // Apply search filter
        if ($search) {
            $products->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search)
                          ->orWhere('product_id', $search);
                }
                $query->orWhereHas('product', function($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%')
                              ->orWhere('type', 'like', '%' . $search . '%');
                      });
            });
        }

        // Apply type filter
        if ($type) {
            $products->whereHas('product', function($query) use ($type) {
                $query->where('type', $type);
            });
        }

        // Apply sorting
        if ($sort) {
            switch ($sort) {
                case 'name_asc':
                    $products->join('products', 'company_products.product_id', '=', 'products.id')
                            ->orderBy('products.name', 'asc')
                            ->select('company_products.*');
                    break;
                case 'name_desc':
                    $products->join('products', 'company_products.product_id', '=', 'products.id')
                            ->orderBy('products.name', 'desc')
                            ->select('company_products.*');
                    break;
                case 'stock_asc':
                    $products->orderBy('available_stock', 'asc');
                    break;
                case 'stock_desc':
                    $products->orderBy('available_stock', 'desc');
                    break;
                case 'price_asc':
                    $products->orderBy('sale_price', 'asc');
                    break;
                case 'price_desc':
                    $products->orderBy('sale_price', 'desc');
                    break;
                default:
                    $products->latest();
            }
        } else {
            $products->latest();
        }

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'companyProducts' => $products->get(),
            ]);
        }

        return inertia('Company/Products/Index', [
            'companyProducts' => $products->get()
        ]);
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
