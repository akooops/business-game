<?php

namespace App\Http\Controllers\Company;

use App\Services\ProductionService;
use Illuminate\Http\Request;
use App\Models\CompanyMachine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\ProductionOrders\ProduceProductRequest;
use App\Http\Requests\Company\ProductionOrders\CancelProductionOrderRequest;
use App\Models\Product;
use App\Models\ProductionOrder;

class ProductionOrdersController extends Controller
{

    public function index(Request $request){
        $company = $request->company;   
        
        $productionOrders = ProductionOrder::whereHas('companyMachine', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->with(['product', 'companyMachine', 'companyMachine.machine'])->latest();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'productionOrders' => $productionOrders->get(),
            ]);
        }

        return inertia('Company/ProductionOrders/Index');
    }

    public function produce(ProduceProductRequest $request, CompanyMachine $companyMachine){
        $product = Product::find($request->product_id);

        ProductionService::startProduction($companyMachine, $product, $request->quantity);

        return response()->json([
            'status' => 'success',
            'message' => 'Production order created successfully!',
        ]);
    }

    public function cancel(CancelProductionOrderRequest $request, ProductionOrder $productionOrder){
        ProductionService::cancelProductionOrder($productionOrder);

        return response()->json([
            'status' => 'success',
            'message' => 'Production order cancelled successfully!',
        ]);
    }
} 