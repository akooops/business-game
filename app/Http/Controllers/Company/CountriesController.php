<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Http\Controllers\Controller;

class CountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));
        
        $countries = Country::latest();

        // Apply search filter
        if ($search) {
            $countries->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $countries = $countries->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'countries' => $countries->items(),
                'pagination' => IndexService::handlePagination($countries)
            ]);
        }

        return inertia('Company/Countries/Index', [
            'countries' => $countries->items(),
            'pagination' => IndexService::handlePagination($countries)
        ]);
    }
} 