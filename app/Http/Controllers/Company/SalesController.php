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

        // Filter parameters
        $wilayaId = IndexService::checkIfNumber($request->query('wilaya_id'));
        $productId = IndexService::checkIfNumber($request->query('product_id'));
        $status = IndexService::checkIfSearchEmpty($request->query('status'));

        $minInitiatedAt = IndexService::checkIfEmpty($request->query('min_initiated_at'));
        $maxInitiatedAt = IndexService::checkIfEmpty($request->query('max_initiated_at'));

        $minConfirmedAt = IndexService::checkIfEmpty($request->query('min_confirmed_at'));
        $maxConfirmedAt = IndexService::checkIfEmpty($request->query('max_confirmed_at'));

        $minDeliveredAt = IndexService::checkIfEmpty($request->query('min_delivered_at'));
        $maxDeliveredAt = IndexService::checkIfEmpty($request->query('max_delivered_at'));
        
        $company = $request->company;

        $sales = $company->sales()->with(['product', 'wilaya'])->latest();

        if($wilayaId){
            $sales->where('wilaya_id', $wilayaId);
        }

        if($productId){
            $sales->where('product_id', $productId);
        }


        if($status){
            $sales->where('status', $status);
        }

        if($minInitiatedAt){
            $sales->where('initiated_at', '>=', $minInitiatedAt);
        }

        if($maxInitiatedAt){
            $sales->where('initiated_at', '<=', $maxInitiatedAt);
        }

        if($minConfirmedAt){
            $sales->where('confirmed_at', '>=', $minConfirmedAt);
        }

        if($maxConfirmedAt){
            $sales->where('confirmed_at', '<=', $maxConfirmedAt);
        }

        if($minDeliveredAt){
            $sales->where('delivered_at', '>=', $minDeliveredAt);    
        }

        if($maxDeliveredAt){
            $sales->where('delivered_at', '<=', $maxDeliveredAt);
        }

        if ($search) {
            $sales->where(function($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('wilaya', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('product', function($q) use ($search) {
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

        if($request->expectsJson() || $request->hasHeader('X-Requested-With')){
            return response()->json([
                'status' => 'success',
                'message' => 'Sale confirmed successfully!'
            ]);
        }

        return inertia('Company/Sales/Index', [
            'success' => 'Sale confirmed successfully!'
        ]);
    }
}
