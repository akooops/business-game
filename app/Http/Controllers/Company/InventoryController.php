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

        $company = $request->company;
        $inventoryMovements = $company->inventoryMovements()->with(['product'])->latest();

        // Apply search filter
        if ($search) {
            $inventoryMovements->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('movement_type', 'like', '%' . $search . '%')
                      ->orWhere('notes', 'like', '%' . $search . '%')
                      ->orWhereHas('product', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhere('type', 'like', '%' . $search . '%');
                      });
            });
        }

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
