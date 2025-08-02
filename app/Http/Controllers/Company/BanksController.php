<?php

namespace App\Http\Controllers\Company;

use App\Models\Bank;
use Illuminate\Http\Request;

class BanksController extends Controller
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

        return inertia('Company/Banks/Index');
    }
}
