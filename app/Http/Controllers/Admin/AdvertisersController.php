<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Advertisers\UpdateAdvertiserRequest;
use App\Http\Requests\Admin\Advertisers\StoreAdvertiserRequest;
use App\Models\Advertiser;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FileService;
use App\Services\IndexService;
use App\Services\SalesService;

class AdvertisersController extends Controller
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

        $advertisers = Advertiser::latest();       

        if ($search) {
            $advertisers->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $advertisers = $advertisers->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'advertisers' => $advertisers->items(),
                'pagination' => IndexService::handlePagination($advertisers)
            ]);
        }

        return inertia('Admin/Advertisers/Index', [
            'advertisers' => $advertisers->items(),
            'pagination' => IndexService::handlePagination($advertisers)
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Advertisers/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdvertiserRequest $request)
    {
        $advertiser = Advertiser::create($request->validated());

        if($request->has('file')){
            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the bank
            FileService::linkModel($file, 'advertiser', $advertiser->id, 1);
        }

        return inertia('Admin/Advertisers/Index', [
            'success' => 'Advertiser created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Advertiser $advertiser)
    {    
        return inertia('Admin/Advertisers/Show', compact('advertiser'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Advertiser $advertiser)
    {
        return inertia('Admin/Advertisers/Edit', compact('advertiser'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Advertiser $advertiser, UpdateAdvertiserRequest $request)
    {
        $advertiser->update($request->validated());

        if($request->file('file')){
            //Delete the old file if it exists
            if($advertiser->logo){
                FileService::delete($advertiser->logo);
            }

            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the bank
            FileService::linkModel($file, 'advertiser', $advertiser->id, 1);
        }
    
        return inertia('Admin/Advertisers/Index', [
            'success' => 'Advertiser updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advertiser $advertiser)
    {
        $advertiser->delete();

        return redirect()->route('admin.advertisers.index')
                        ->with('success','Advertiser deleted successfully');
    }
}
