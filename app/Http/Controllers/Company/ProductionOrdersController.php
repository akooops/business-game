<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Machines\SetupMachineRequest;
use App\Http\Requests\Company\Machines\AssignEmployeeRequest;
use App\Services\FileService;
use App\Services\IndexService;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\CompanyMachine;
use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\ProductionOrders\ProduceProductRequest;
use App\Models\Product;

class ProductionOrdersController extends Controller
{
    public function produce(ProduceProductRequest $request, CompanyMachine $companyMachine){
        $product = Product::find($request->product_id);

        ProductionService::startProduction($companyMachine, $product, $request->quantity);

        return response()->json([
            'status' => 'success',
            'message' => 'Production order created successfully!',
        ]);
    }
} 