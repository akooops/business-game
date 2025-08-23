<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Wilaya;
use App\Http\Controllers\Controller;

class WilayasController extends Controller
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

        $wilayas = Wilaya::latest();

        // Apply search filter
        if ($search) {
            $wilayas->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $wilayas = $wilayas->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'wilayas' => $wilayas->items(),
                'pagination' => IndexService::handlePagination($wilayas)
            ]);
        }

        return inertia('Company/Wilayas/Index');
    }
} 