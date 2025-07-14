<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Technologies\ResearchTechnolgyRequest;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Technology;
use App\Services\TechnolgiesResearchService;

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

        $company = $request->company;
        $technologies = $company->companyTechnologies()->with(['technology', 'technology.products'])->latest()->orderBy('completed_at', 'asc');

        // Apply search filter
        if ($search) {
            $technologies->whereHas('technology', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('level', 'like', '%' . $search . '%');
            });
        }

        $technologies = $technologies->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'technologies' => $technologies->items(),
                'pagination' => IndexService::handlePagination($technologies)
            ]);
        }

        return inertia('Company/Technologies/Index');
    }

    public function researchPage(Request $request)
    {   
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        // 1. Get company's current technologies
        $company = $request->company;
        $companyTechnologies = $company->technologies()->pluck('technology_id');
        $currentResearchLevel = $company->research_level;

        // 2. Get technologies that are NOT in company's technologies AND have level >= current research level
        $technologies = Technology::with('products')
            ->whereNotIn('id', $companyTechnologies)
            ->where('level', '<=', $currentResearchLevel + 1)
            ->orderBy('level', 'asc');

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
                'pagination' => IndexService::handlePagination($technologies),
            ]);
        }

        return inertia('Company/Technologies/ResearchPage');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function research(ResearchTechnolgyRequest $request, Technology $technology)
    {
        TechnolgiesResearchService::researchTechnology($request->company, $technology);

        if($request->expectsJson() || $request->hasHeader('X-Requested-With')){
            return response()->json([
                'status' => 'success',
                'message' => 'Technology research started successfully!'
            ]);
        }

        return inertia('Company/Technologies/Index', [
            'success' => 'Technology research started successfully!'
        ]);
    }
}
