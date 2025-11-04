<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Companies\UpdateCompanyRequest;
use App\Http\Requests\Admin\Companies\StoreCompanyRequest;
use App\Models\Ad;
use App\Models\Company;
use App\Models\CompanyMachine;
use App\Models\CompanyProduct;
use App\Models\CompanyTechnology;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhereHas('user', function($query) use ($search) {
                        $query->where('firstname', 'like', '%' . $search . '%')
                              ->orWhere('lastname', 'like', '%' . $search . '%')
                              ->orWhere('username', 'like', '%' . $search . '%')
                              ->orWhere('email', 'like', '%' . $search . '%');
                      });
            });
        }

        $companies = $companies->paginate($perPage, ['*'], 'page', $page);
        
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'companies' => $companies->items(),
                'pagination' => IndexService::handlePagination($companies)
            ]);
        }

        return inertia('Admin/Companies/Index', [
            'companies' => $companies->items(),
            'pagination' => IndexService::handlePagination($companies)
        ]);
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
            'unpaid_loans' => $request->unpaid_loans,
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
            'unpaid_loans' => $request->unpaid_loans,
            'carbon_footprint' => $request->carbon_footprint,
            'research_level' => $request->research_level,
        ]);

        $products = Product::where('technology_id', null)->orWhereHas('technology', function($query) use ($company) {
            $query->where('level', '<=', $company->research_level);
        })->get();

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
        $companyId = $company->id;
        $userId = $company->user_id;

        DB::beginTransaction();

        try {
            // Delete all company-related data (same logic as ResetCompany command)
            
            // Delete Ads
            Ad::where('company_id', $companyId)->delete();
            
            // Delete Notifications (use user_id)
            if ($userId) {
                Notification::where('user_id', $userId)->delete();
            }
            
            // Delete Transactions
            Transaction::where('company_id', $companyId)->delete();
            
            // Delete Sales
            Sale::where('company_id', $companyId)->delete();
            
            // Delete Purchases
            Purchase::where('company_id', $companyId)->delete();
            
            // Delete Production Orders and Maintenances (use company_machine_id)
            $companyMachineIds = CompanyMachine::where('company_id', $companyId)->pluck('id')->toArray();
            if (count($companyMachineIds) > 0) {
                ProductionOrder::whereIn('company_machine_id', $companyMachineIds)->delete();
                Maintenance::whereIn('company_machine_id', $companyMachineIds)->delete();
            }
            
            // Delete Loans
            Loan::where('company_id', $companyId)->delete();
            
            // Delete Inventory Movements
            InventoryMovement::where('company_id', $companyId)->delete();
            
            // Delete Employees
            Employee::where('company_id', $companyId)->delete();
            
            // Delete Company Technologies
            CompanyTechnology::where('company_id', $companyId)->delete();
            
            // Delete Company Products
            CompanyProduct::where('company_id', $companyId)->delete();
            
            // Delete Company Machines
            CompanyMachine::where('company_id', $companyId)->delete();
            
            // Delete the company
            $company->delete();
            
            // Delete the user
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->delete();
                }
            }

            DB::commit();

            return redirect()->route('admin.companies.index')
                            ->with('success', 'Company deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.companies.index')
                            ->with('error', 'Failed to delete company: ' . $e->getMessage());
        }
    }
}
