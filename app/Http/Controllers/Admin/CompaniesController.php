<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Companies\UpdateCompanyRequest;
use App\Http\Requests\Admin\Companies\StoreCompanyRequest;
use App\Models\Company;
use App\Models\Product;
use App\Models\CompanyProduct;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FileService;
use App\Services\IndexService;
use App\Services\SalesService;

class CompaniesController extends Controller
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

        $companies = Company::with('user')->latest();       

        if ($search) {
            $companies->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhereHas('user', function($query) use ($search) {
                        $query->where('firstname', 'like', '%' . $search . '%')
                              ->orWhere('lastname', 'like', '%' . $search . '%')
                              ->orWhere('username', 'like', '%' . $search . '%')
                              ->orWhere('email', 'like', '%' . $search . '%');
                      });
            });
        }

        $companies = $companies->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'companies' => $companies->items(),
                'pagination' => IndexService::handlePagination($companies)
            ]);
        }

        return inertia('Admin/Companies/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Companies/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $company = Company::create([
            'user_id' => $user->id,
            'funds' => $request->funds,
            'carbon_footprint' => $request->carbon_footprint,
            'research_level' => $request->research_level,
        ]);

        $products = Product::where('technology_id', null)->get();

        foreach($products as $product){
            // Check if product already exists
            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
            if($companyProduct){
                continue;
            }

            CompanyProduct::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'total_stock' => 0,
                'in_sale_stock' => 0,
                'sale_price' => SalesService::getCurrentGameweekProductMarketPrice($product),
            ]);
        }
    
        if($request->has('file')){
            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the user
            FileService::linkModel($file, 'user', $user->id, 1);
        }

        return inertia('Admin/Companies/Index', [
            'success' => 'Company created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {    
        $company->load('user');
        return inertia('Admin/Companies/Show', compact('company'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $company->load('user');

        return inertia('Admin/Companies/Edit', compact('company'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Company $company, UpdateCompanyRequest $request)
    {
        if($request->get('password') != null){
            $company->user->update([
                'password' => $request->get('password'),
            ]);
        }

        $company->user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        $company->update([
            'funds' => $request->funds,
            'carbon_footprint' => $request->carbon_footprint,
            'research_level' => $request->research_level,
        ]);

        $products = Product::where('technology_id', null)->get();

        foreach($products as $product){
            // Check if product already exists
            $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();
            if($companyProduct){
                continue;
            }

            CompanyProduct::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'available_stock' => 0,
                'sale_price' => SalesService::getCurrentGameweekProductMarketPrice($product),
            ]);
        }

        if($request->file('file')){
            //Delete the old file if it exists
            if($company->user->file){
                FileService::delete($company->user->file);
            }

            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the user
            FileService::linkModel($file, 'user', $company->user->id, 1);
        }
    
        return inertia('Admin/Companies/Index', [
            'success' => 'Company updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->user->delete();

        return redirect()->route('admin.companies.index')
                        ->with('success','Company deleted successfully');
    }
}
