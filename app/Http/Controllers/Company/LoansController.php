<?php

namespace App\Http\Controllers\Company;

use App\Models\Bank;
use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Requests\Company\Loans\BorrowMoneyRequest;
use App\Http\Requests\Company\Loans\PayLoanRequest;
use App\Services\LoansService;

class LoansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $banks = Bank::latest();       

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'banks' => $banks->get(),
            ]);
        }

        return inertia('Company/Loans/Index');
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
