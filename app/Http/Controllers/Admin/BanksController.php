<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Banks\UpdateBankRequest;
use App\Http\Requests\Admin\Banks\StoreBankRequest;
use App\Models\Bank;
use App\Models\Product;
use App\Models\BankProduct;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FileService;
use App\Services\IndexService;
use App\Services\SalesService;

class BanksController extends Controller
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

        $banks = Bank::latest();       

        if ($search) {
            $banks->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $banks = $banks->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'banks' => $banks->items(),
                'pagination' => IndexService::handlePagination($banks)
            ]);
        }

        return inertia('Admin/Banks/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Banks/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBankRequest $request)
    {
        $bank = Bank::create($request->validated());

        if($request->has('file')){
            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the bank
            FileService::linkModel($file, 'bank', $bank->id, 1);
        }

        return inertia('Admin/Banks/Index', [
            'success' => 'Bank created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Bank $bank)
    {    
        return inertia('Admin/Banks/Show', compact('bank'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Bank $bank)
    {
        return inertia('Admin/Banks/Edit', compact('bank'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Bank $bank, UpdateBankRequest $request)
    {
        $bank->update($request->validated());

        if($request->file('file')){
            //Delete the old file if it exists
            if($bank->logo){
                FileService::delete($bank->logo);
            }

            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the bank
            FileService::linkModel($file, 'bank', $bank->id, 1);
        }
    
        return inertia('Admin/Banks/Index', [
            'success' => 'Bank updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('admin.banks.index')
                        ->with('success','Bank deleted successfully');
    }
}
