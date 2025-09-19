<?php

namespace App\Http\Controllers\Company;

use App\Models\Ad;
use App\Models\Advertiser;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Ads\CreateAdRequest;
use App\Services\AdsService;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company = $request->company;
        $ads = $company->ads()->with(['advertiser', 'product'])->latest();       

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'ads' => $ads->get(),
            ]);
        }

        return inertia('Company/Ads/Index');
    }

    public function store(CreateAdRequest $request)
    {
        $validated = $request->validated();

        $company = $request->company;
        $advertiser = Advertiser::find($validated['advertiser_id']);
        $product = Product::find($validated['product_id']);

        AdsService::createAdPackage($company, $advertiser, $product);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad package created successfully!',
        ]);
    }
}
