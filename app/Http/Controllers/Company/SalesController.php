<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Sales\ConfirmSaleRequest;
use App\Models\Sale;
use App\Services\SalesService;

class SalesController extends Controller
{
    public function index(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));

        $company = $request->company;

        $sales = $company->sales()->with(['product', 'wilaya'])->latest();
        $sales = $sales->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'sales' => $sales->items(),
                'pagination' => IndexService::handlePagination($sales),
            ]);
        }

        return inertia('Company/Sales/Index', [
            'sales' => $sales->items(),
            'pagination' => IndexService::handlePagination($sales)
        ]);
    }

    public function confirm(ConfirmSaleRequest $request, Sale $sale){
        SalesService::confirmSale($sale);

        return response()->json([
            'status' => 'success',
            'message' => 'Sale confirmed successfully!'
        ]);
    }
}
