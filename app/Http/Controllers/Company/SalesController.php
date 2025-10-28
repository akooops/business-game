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
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        $company = $request->company;

        $sales = $company->sales()->with(['product', 'wilaya'])->latest();

        // Apply search filter
        if ($search) {
            $sales->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('status', 'like', '%' . $search . '%')
                      ->orWhereHas('product', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhere('type', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('wilaya', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
            });
        }

        $sales = $sales->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'sales' => $sales->items(),
                'pagination' => IndexService::handlePagination($sales),
            ]);
        }

        return inertia('Company/Sales/Index');
    }

    public function confirm(ConfirmSaleRequest $request, Sale $sale){
        SalesService::confirmSale($sale);

        return response()->json([
            'status' => 'success',
            'message' => 'Sale confirmed successfully!'
        ]);
    }
}
