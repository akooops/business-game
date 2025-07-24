<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SuppliersController extends Controller
{
    public function index(Request $request)
    {   
        $suppliers = Supplier::with(['country', 'wilaya', 'supplierProducts', 'supplierProducts.product'])->latest();

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'suppliers' => $suppliers->get()
            ]);
        }

        return inertia('Company/Suppliers/Index');
    }
}
