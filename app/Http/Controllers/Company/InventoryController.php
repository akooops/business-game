<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Services\ProcurementService;
use App\Http\Requests\Company\Purchases\PurchaseProductRequest;
use App\Models\Product;
use App\Models\Purchase;

class InventoryController extends Controller
{
    public function index(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // Filter parameters
        $productId = IndexService::checkIfNumber($request->query('product_id'));

        $movementType = IndexService::checkIfSearchEmpty($request->query('movement_type'));
        $referenceType = IndexService::checkIfSearchEmpty($request->query('reference_type'));

        $minMovedAt = IndexService::checkIfEmpty($request->query('min_moved_at'));
        $maxMovedAt = IndexService::checkIfEmpty($request->query('max_moved_at'));

        $company = $request->company;

        $inventoryMovements = $company->inventoryMovements()->with(['product'])->latest();

        if($movementType){
            $inventoryMovements->where('movement_type', $movementType);
        }

        if($productId){
            $inventoryMovements->where('product_id', $productId);
        }

        if($referenceType){
            $inventoryMovements->where('reference_type', $referenceType);
        }

        if($minMovedAt){
            $inventoryMovements->where('moved_at', '>=', $minMovedAt);
        }

        if($maxMovedAt){
            $inventoryMovements->where('moved_at', '<=', $maxMovedAt);
        }

        if ($search) {
            $inventoryMovements->where(function($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('movement_type', 'like', '%' . $search . '%')
                    ->orWhereHas('product', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $inventoryMovements = $inventoryMovements->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'inventoryMovements' => $inventoryMovements->items(),
                'pagination' => IndexService::handlePagination($inventoryMovements),
            ]);
        }

        return inertia('Company/Inventory/Index');
    }
}
