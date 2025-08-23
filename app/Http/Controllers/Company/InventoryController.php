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
        $company = $request->company;
        $inventoryMovements = $company->inventoryMovements()->with(['product'])->latest();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'inventoryMovements' => $inventoryMovements->get(),
            ]);
        }

        return inertia('Company/Inventory/Index');
    }
}
