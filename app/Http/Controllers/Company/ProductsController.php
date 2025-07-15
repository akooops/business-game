<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Technologies\ResearchTechnolgyRequest;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Technology;
use App\Services\TechnolgiesResearchService;

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

        // Apply type filter
        $typeFilter = IndexService::checkIfSearchEmpty($request->query('type'));

        $company = $request->company;
        $products = $company->companyProducts()->with(['product', 'product.recipes.material'])->latest();

        // Apply search filter
        if ($search) {
            $products->whereHas('product', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
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

        return inertia('Company/Products/Index');
    }
}
