<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Wilayas\UpdateWilayaRequest;
use App\Http\Requests\Admin\Wilayas\StoreWilayaRequest;
use App\Services\FileService;
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

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'wilayas' => $wilayas->items(),
                'pagination' => IndexService::handlePagination($wilayas)
            ]);
        }

        return inertia('Admin/Wilayas/Index', [
            'wilayas' => $wilayas->items(),
            'pagination' => IndexService::handlePagination($wilayas)
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return inertia('Admin/Wilayas/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWilayaRequest $request)
    {
        $wilaya = Wilaya::create($request->validated());

        return inertia('Admin/Wilayas/Index', [
            'success' => 'Wilaya created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Wilaya $wilaya)
    {    
        return inertia('Admin/Wilayas/Show', compact('wilaya'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Wilaya $wilaya)
    {
        return inertia('Admin/Wilayas/Edit', compact('wilaya'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Wilaya $wilaya, UpdateWilayaRequest $request)
    {
        $wilaya->update($request->validated());

        return inertia('Admin/Wilayas/Index', [
            'success' => 'Wilaya updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wilaya $wilaya)
    {
        $wilaya->delete();

        return inertia('Admin/Wilayas/Index', [
            'success' => 'Wilaya deleted successfully!'
        ]);
    }
} 