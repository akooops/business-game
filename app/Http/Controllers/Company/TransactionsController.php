<?php

namespace App\Http\Controllers\Company;

use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionsController extends Controller
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
        
        $company = auth()->user()->company;
        
        $transactions = Transaction::where('company_id', $company->id)->latest();

        // Apply search filter
        if ($search) {
            $transactions->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $transactions = $transactions->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'transactions' => $transactions->items(),
                'pagination' => IndexService::handlePagination($transactions)
            ]);
        }

        return inertia('Company/Transactions/Index', [
            'transactions' => $transactions->items(),
            'pagination' => IndexService::handlePagination($transactions)
        ]);
    }
} 