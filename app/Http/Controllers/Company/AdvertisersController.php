<?php

namespace App\Http\Controllers\Company;

use App\Models\Advertiser;
use Illuminate\Http\Request;

class AdvertisersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $advertisers = Advertiser::latest();       

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'advertisers' => $advertisers->get(),
            ]);
        }

        return inertia('Company/Advertisers/Index');
    }
}
