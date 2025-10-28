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
use App\Services\IndexService;

class ProductionOrdersController extends Controller
{

    public function index(Request $request){
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));

        $company = $request->company;   
        
        $productionOrders = ProductionOrder::whereHas('companyMachine', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->with(['product', 'companyMachine', 'companyMachine.machine'])->latest();

        $productionOrders = $productionOrders->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'productionOrders' => $productionOrders->items(),
                'pagination' => IndexService::handlePagination($productionOrders)
            ]);
        }

        return inertia('Company/ProductionOrders/Index', [
            'productionOrders' => $productionOrders->items(),
            'pagination' => IndexService::handlePagination($productionOrders)
        ]);
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