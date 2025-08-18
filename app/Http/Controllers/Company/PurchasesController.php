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
        $company = $request->company;
        $purchases = $company->purchases()->with('supplier', 'product')->latest();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'purchases' => $purchases->get(),
            ]);
        }

        return inertia('Company/Purchases/Index');
    }

    public function purchasePage(Request $request){
        return inertia('Company/Purchases/PurchasePage');
    }

    public function purchase(PurchaseProductRequest $request){
        $supplier = Supplier::find($request->supplier_id);
        $product = Product::find($request->product_id);
        $quantity = $request->quantity;

        ProcurementService::purchase($request->company, $supplier, $product, $quantity);

        return response()->json([
            'status' => 'success',
            'message' => 'Product purchased successfully!'
        ]);
    }
}
