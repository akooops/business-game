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
        $type = $request->query('type');
        $sort = $request->query('sort');

        $company = $request->company;
        $inventoryMovements = $company->inventoryMovements()->with(['product']);

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

        // Apply movement type filter
        if ($type) {
            $inventoryMovements->where('movement_type', $type);
        }

        // Apply sorting
        if ($sort) {
            switch ($sort) {
                case 'date_desc':
                    $inventoryMovements->orderBy('moved_at', 'desc');
                    break;
                case 'date_asc':
                    $inventoryMovements->orderBy('moved_at', 'asc');
                    break;
                case 'quantity_desc':
                    $inventoryMovements->orderBy('original_quantity', 'desc');
                    break;
                case 'quantity_asc':
                    $inventoryMovements->orderBy('original_quantity', 'asc');
                    break;
                case 'product_asc':
                    $inventoryMovements->join('products', 'inventory_movements.product_id', '=', 'products.id')
                                      ->orderBy('products.name', 'asc')
                                      ->select('inventory_movements.*');
                    break;
                case 'product_desc':
                    $inventoryMovements->join('products', 'inventory_movements.product_id', '=', 'products.id')
                                      ->orderBy('products.name', 'desc')
                                      ->select('inventory_movements.*');
                    break;
                default:
                    $inventoryMovements->latest();
            }
        } else {
            $inventoryMovements->latest();
        }

        $inventoryMovements = $inventoryMovements->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'inventoryMovements' => $inventoryMovements->items(),
                'pagination' => IndexService::handlePagination($inventoryMovements)
            ]);
        }

        return inertia('Company/Inventory/Index', [
            'inventoryMovements' => $inventoryMovements->items(),
            'pagination' => IndexService::handlePagination($inventoryMovements)
        ]);
    }
}
