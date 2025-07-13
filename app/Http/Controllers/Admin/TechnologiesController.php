<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Technologies\UpdateTechnologyRequest;
use App\Http\Requests\Admin\Technologies\StoreTechnologyRequest;
use App\Services\FileService;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Technology;

class TechnologiesController extends Controller
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

        // Filter parameters
        $levelFilter = IndexService::checkIfSearchEmpty($request->query('level'));
        $researchCostMin = IndexService::checkIfNumber($request->query('research_cost_min'));
        $researchCostMax = IndexService::checkIfNumber($request->query('research_cost_max'));
        $researchTimeDaysMin = IndexService::checkIfNumber($request->query('research_time_days_min'));
        $researchTimeDaysMax = IndexService::checkIfNumber($request->query('research_time_days_max'));

        $technologies = Technology::orderBy('level', 'asc');

        // Apply level filter
        if ($levelFilter) {
            $technologies->where('level', $levelFilter);
        }

        // Apply research cost range filters
        if ($researchCostMin) {
            $technologies->where('research_cost', '>=', $researchCostMin);
        }

        if ($researchCostMax) {
            $technologies->where('research_cost', '<=', $researchCostMax);
        }

        // Apply research time days range filters
        if ($researchTimeDaysMin) {
            $technologies->where('research_time_days', '>=', $researchTimeDaysMin);
        }

        if ($researchTimeDaysMax) {
            $technologies->where('research_time_days', '<=', $researchTimeDaysMax);
        }

        // Apply search filter
        if ($search) {
            $technologies->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('level', 'like', '%' . $search . '%')
                      ->orWhere('research_cost', 'like', '%' . $search . '%')
                      ->orWhere('research_time_days', 'like', '%' . $search . '%');
            });
        }

        $technologies = $technologies->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'technologies' => $technologies->items(),
                'pagination' => IndexService::handlePagination($technologies)
            ]);
        }

        return inertia('Admin/Technologies/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Technologies/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTechnologyRequest $request)
    {
        $technology = Technology::create($request->validated());

        if($request->has('file')){
            $file = FileService::upload($request->file('file'));

            //Link the file to the technology
            FileService::linkModel($file, 'technology', $technology->id, 1);
        }

        return inertia('Admin/Technologies/Index', [
            'success' => 'Technology created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Technology $technology)
    {    
        return inertia('Admin/Technologies/Show', compact('technology'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Technology $technology)
    {
        return inertia('Admin/Technologies/Edit', compact('technology'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Technology $technology, UpdateTechnologyRequest $request)
    {
        $technology->update($request->validated());

        if($request->file('file')){            
            //Delete the old file if it exists
            if($technology->image){
                FileService::delete($technology->image);
            }

            $file = FileService::upload($request->file('file'));

            //Link the file to the technology
            FileService::linkModel($file, 'technology', $technology->id, 1);
        }
    
        return inertia('Admin/Technologies/Index', [
            'success' => 'Technology updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Technology $technology)
    {
        $technology->delete();

        return redirect()->route('admin.technologies.index')
                        ->with('success','Technology deleted successfully');
    }
}
