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
        $type = $request->query('type');
        $status = $request->query('status');
        $sort = $request->query('sort');

        $company = $request->company;

        $sales = $company->sales()->with(['product', 'wilaya']);

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

        // Apply product type filter
        if ($type) {
            $sales->whereHas('product', function($query) use ($type) {
                $query->where('type', $type);
            });
        }

        // Apply status filter
        if ($status) {
            $sales->where('status', $status);
        }

        // Apply sorting
        if ($sort) {
            switch ($sort) {
                case 'date_desc':
                    $sales->orderBy('initiated_at', 'desc');
                    break;
                case 'date_asc':
                    $sales->orderBy('initiated_at', 'asc');
                    break;
                case 'quantity_desc':
                    $sales->orderBy('quantity', 'desc');
                    break;
                case 'quantity_asc':
                    $sales->orderBy('quantity', 'asc');
                    break;
                case 'price_desc':
                    $sales->orderBy('sale_price', 'desc');
                    break;
                case 'price_asc':
                    $sales->orderBy('sale_price', 'asc');
                    break;
                case 'status_asc':
                    $sales->orderBy('status', 'asc');
                    break;
                case 'status_desc':
                    $sales->orderBy('status', 'desc');
                    break;
                default:
                    $sales->latest();
            }
        } else {
            $sales->latest();
        }

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
