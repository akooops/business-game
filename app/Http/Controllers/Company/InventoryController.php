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

        $company = $request->company;
        $inventoryMovements = $company->inventoryMovements()->with(['product'])->latest();

        $inventoryMovements = $inventoryMovements->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'inventoryMovements' => $inventoryMovements->items(),
                'pagination' => IndexService::handlePagination($inventoryMovements)
            ]);
        }

        return inertia('Company/Inventory/Index');
    }
}
