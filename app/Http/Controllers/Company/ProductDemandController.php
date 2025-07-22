<?php

namespace App\Http\Controllers\Company;
        
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDemand;
use App\Services\IndexService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class ProductDemandController extends Controller
{
    /**
     * Display the product demand management page
     */
    public function index(Request $request)
    {
        if($request->has('product_id')){
            $product = Product::findOrFail($request->product_id);

            $demandVisibilityAheadWeeks = SettingsService::getDemandVisibilityAheadWeeks();
            $currentGameWeek  = SettingsService::getCurrentGameWeek();

            $productDemands = $product->demands()->where('gameweek', '<=', $currentGameWeek + $demandVisibilityAheadWeeks)->latest();

            if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
                return response()->json([
                    'productDemands' => $productDemands->get()
                ]);
            }
        }

        return inertia('Company/ProductDemand/Index');
    }
} 