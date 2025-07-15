<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Services\ProcurementService;
use App\Http\Requests\Company\Purchases\PurchaseProductRequest;
use App\Models\Product;
use App\Models\Purchase;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // Filter parameters
        $supplierId = IndexService::checkIfNumber($request->query('supplier_id'));
        $productId = IndexService::checkIfNumber($request->query('product_id'));
        $status = IndexService::checkIfSearchEmpty($request->query('status'));

        $minTotalCost = IndexService::checkIfNumber($request->query('min_total_cost'));
        $maxTotalCost = IndexService::checkIfNumber($request->query('max_total_cost'));

        $minOrderedAt = IndexService::checkIfEmpty($request->query('min_ordered_at'));
        $maxOrderedAt = IndexService::checkIfEmpty($request->query('max_ordered_at'));

        $minEstimatedDeliveredAt = IndexService::checkIfEmpty($request->query('min_estimated_delivered_at'));
        $maxEstimatedDeliveredAt = IndexService::checkIfEmpty($request->query('max_estimated_delivered_at'));

        $minDeliveredAt = IndexService::checkIfEmpty($request->query('min_delivered_at'));
        $maxDeliveredAt = IndexService::checkIfEmpty($request->query('max_delivered_at'));

        $company = $request->company;

        $purchases = $company->purchases()->with(['supplier', 'product'])->latest();

        if($supplierId){
            $purchases->where('supplier_id', $supplierId);
        }

        if($productId){
            $purchases->where('product_id', $productId);
        }


        if($status){
            $purchases->where('status', $status);
        }

        if($minTotalCost){
            $purchases->where('total_cost', '>=', $minTotalCost);
        }

        if($maxTotalCost){
            $purchases->where('total_cost', '<=', $maxTotalCost);
        }

        if($minOrderedAt){
            $purchases->where('ordered_at', '>=', $minOrderedAt);
        }

        if($maxOrderedAt){
            $purchases->where('ordered_at', '<=', $maxOrderedAt);
        }

        if($minEstimatedDeliveredAt){
            $purchases->where('estimated_delivered_at', '>=', $minEstimatedDeliveredAt);    
        }

        if($maxEstimatedDeliveredAt){
            $purchases->where('estimated_delivered_at', '<=', $maxEstimatedDeliveredAt);
        }

        if($minDeliveredAt){
            $purchases->where('delivered_at', '>=', $minDeliveredAt);
        }

        if($maxDeliveredAt){
            $purchases->where('delivered_at', '<=', $maxDeliveredAt);
        }

        if ($search) {
            $purchases->where(function($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('supplier', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('product', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $purchases = $purchases->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'purchases' => $purchases->items(),
                'pagination' => IndexService::handlePagination($purchases),
            ]);
        }

        return inertia('Company/Purchases/Index');
    }

    public function purchasePage(Request $request){
        return inertia('Company/Purchases/PurchasePage');
    }

    public function purchase(PurchaseProductRequest $request, Product $product){
        $supplier = Supplier::find($request->supplier_id);

        ProcurementService::purchase($request->company, $supplier, $product, $request->quantity);

        if($request->expectsJson() || $request->hasHeader('X-Requested-With')){
            return response()->json([
                'status' => 'success',
                'message' => 'Product purchased successfully!'
            ]);
        }

        return inertia('Company/Purchases/Index', [
            'success' => 'Product purchased successfully!'
        ]);
    }
}
