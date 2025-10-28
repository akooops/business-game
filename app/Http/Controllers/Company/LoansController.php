<?php

namespace App\Http\Controllers\Company;

use App\Models\Bank;
use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Loans\BorrowMoneyRequest;
use App\Http\Requests\Company\Loans\PayLoanRequest;
use App\Services\LoansService;
use App\Services\IndexService;

class LoansController extends Controller
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

        $company = $request->company;
        $loans = $company->loans()->with('bank')->latest();   

        $loans = $loans->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'loans' => $loans->items(),
                'pagination' => IndexService::handlePagination($loans)
            ]);
        }

        return inertia('Company/Loans/Index', [
            'loans' => $loans->items(),
            'pagination' => IndexService::handlePagination($loans)
        ]);
    }

    public function store(BorrowMoneyRequest $request)
    {
        $validated = $request->validated();

        $company = $request->company;
        $bank = Bank::find($validated['bank_id']);

        LoansService::borrowMoney($company, $bank, $validated['amount']);

        return response()->json([
            'status' => 'success',
            'message' => 'Loan created successfully!',
        ]);
    }

    public function pay(PayLoanRequest $request, Loan $loan){
        $company = $request->company;
        LoansService::payLoan($company, $loan);

        return response()->json([
            'status' => 'success',
            'message' => 'Loan paid successfully!',
        ]);
    }
}
