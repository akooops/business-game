<?php

namespace App\Http\Controllers\Company;
        
use App\Http\Controllers\Controller;
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
        // Filter parameters
        $productId = IndexService::checkIfSearchEmpty($request->query('product_id'));

        $demandVisibilityAheadWeeks = SettingsService::getDemandVisibilityAheadWeeks();
        $currentGameWeek  = SettingsService::getCurrentGameWeek();

        $productDemands = ProductDemand::where('gameweek', '<=', $currentGameWeek + $demandVisibilityAheadWeeks);

        // Apply type filter
        if ($productId) {
            $productDemands->where('product_id', $productId);
        }

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'productDemands' => $productDemands->get()
            ]);
        }

        return inertia('Company/ProductDemand/Index');
    }
} 