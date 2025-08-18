<?php

namespace App\Http\Controllers\Company;

use App\Http\Requests\Company\Technologies\ResearchTechnologyRequest;
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
        $company = $request->company;
        $technologies = Technology::with('products')->orderBy('level', 'asc');
        $companyTechnologies = $company->companyTechnologies()->with('technology', 'technology.products')->get();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'technologies' => $technologies->get(),
                'companyTechnologies' => $companyTechnologies,
                'maxResearchLevel' => $technologies->max('level'),
                'currentResearchLevel' => $company->research_level,
            ]);
        }

        return inertia('Company/Technologies/Index');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function research(ResearchTechnologyRequest $request, Technology $technology)
    {
        TechnolgiesResearchService::researchTechnology($request->company, $technology);

        return response()->json([
            'status' => 'success',
            'message' => 'Technology research started successfully!'
        ]);
    }
}
