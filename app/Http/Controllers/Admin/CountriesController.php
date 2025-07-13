<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Countries\UpdateCountryRequest;
use App\Http\Requests\Admin\Countries\StoreCountryRequest;
use App\Services\FileService;
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

        // Filter parameters
        $codeFilter = IndexService::checkIfSearchEmpty($request->query('code'));
        $allowsImportsFilter = $request->query('allows_imports');
        $customsDutiesMin = IndexService::checkIfNumber($request->query('customs_duties_min'));
        $customsDutiesMax = IndexService::checkIfNumber($request->query('customs_duties_max'));
        $tvaMin = IndexService::checkIfNumber($request->query('tva_min'));
        $tvaMax = IndexService::checkIfNumber($request->query('tva_max'));
        $insuranceMin = IndexService::checkIfNumber($request->query('insurance_min'));
        $insuranceMax = IndexService::checkIfNumber($request->query('insurance_max'));
        $freightMin = IndexService::checkIfNumber($request->query('freight_min'));
        $freightMax = IndexService::checkIfNumber($request->query('freight_max'));
        $handlingMin = IndexService::checkIfNumber($request->query('handling_min'));
        $handlingMax = IndexService::checkIfNumber($request->query('handling_max'));
        $minShippingCost = IndexService::checkIfNumber($request->query('min_shipping_cost'));
        $maxShippingCost = IndexService::checkIfNumber($request->query('max_shipping_cost'));
        $minShippingTimeDays = IndexService::checkIfNumber($request->query('min_shipping_time_days'));
        $maxShippingTimeDays = IndexService::checkIfNumber($request->query('max_shipping_time_days'));

        $countries = Country::latest();

        // Apply code filter
        if ($codeFilter) {
            $countries->where('code', $codeFilter);
        }

        // Apply import allowance filter
        if ($allowsImportsFilter !== null) {
            $countries->where('allows_imports', filter_var($allowsImportsFilter, FILTER_VALIDATE_BOOLEAN));
        }

        // Apply customs duties rate range filters
        if ($customsDutiesMin) {
            $countries->where('customs_duties_rate', '>=', $customsDutiesMin);
        }

        if ($customsDutiesMax) {
            $countries->where('customs_duties_rate', '<=', $customsDutiesMax);
        }

        // Apply TVA rate range filters
        if ($tvaMin) {
            $countries->where('tva_rate', '>=', $tvaMin);
        }

        if ($tvaMax) {
            $countries->where('tva_rate', '<=', $tvaMax);
        }

        // Apply insurance rate range filters
        if ($insuranceMin) {
            $countries->where('insurance_rate', '>=', $insuranceMin);
        }

        if ($insuranceMax) {
            $countries->where('insurance_rate', '<=', $insuranceMax);
        }

        // Apply freight cost range filters
        if ($freightMin) {
            $countries->where('freight_cost', '>=', $freightMin);
        }

        if ($freightMax) {
            $countries->where('freight_cost', '<=', $freightMax);
        }

        // Apply handling fee range filters
        if ($handlingMin) {
            $countries->where('port_handling_fee', '>=', $handlingMin);
        }

        if ($handlingMax) {
            $countries->where('port_handling_fee', '<=', $handlingMax);
        }

        // Apply min shipping cost range filters
        if ($minShippingCost) {
            $countries->where('avg_shipping_cost', '>=', $minShippingCost);
        }
        
        if ($maxShippingCost) {
            $countries->where('avg_shipping_cost', '<=', $maxShippingCost);
        }

        // Apply min shipping time range filters
        if ($minShippingTimeDays) {
            $countries->where('avg_shipping_time_days', '>=', $minShippingTimeDays);
        }

        if ($maxShippingTimeDays) {
            $countries->where('avg_shipping_time_days', '<=', $maxShippingTimeDays);
        }
        
        // Apply search filter
        if ($search) {
            $countries->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $countries = $countries->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'countries' => $countries->items(),
                'pagination' => IndexService::handlePagination($countries)
            ]);
        }

        return inertia('Admin/Countries/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return inertia('Admin/Countries/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCountryRequest $request)
    {
        $validated = $request->validated();

        // Create the country
        $country = Country::create($validated);

        // Handle flag upload
        if($request->has('file')){
            $file = FileService::upload($request->file('file'));

            //Link the file to the country
            FileService::linkModel($file, 'country', $country->id, 1);
        }

        return inertia('Admin/Countries/Index', [
            'success' => 'Country created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Country $country)
    {    
        return inertia('Admin/Countries/Show', compact('country'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        return inertia('Admin/Countries/Edit', compact('country'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Country $country, UpdateCountryRequest $request)
    {
        $validated = $request->validated();

        // Update the country
        $country->update($validated);

        // Handle flag upload
        if($request->file('file')){
            //Delete the old flag if it exists
            if($country->flag){
                FileService::delete($country->flag);
            }

            $file = FileService::upload($request->file('file'));

            //Link the file to the country
            FileService::linkModel($file, 'country', $country->id, 1);
        }
    
        return inertia('Admin/Countries/Index', [
            'success' => 'Country updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('admin.countries.index')
                        ->with('success','Country deleted successfully');
    }
} 