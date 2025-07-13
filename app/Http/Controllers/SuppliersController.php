<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Models\Country;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::with(['country', 'wilaya']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('specialties', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('country', function (Builder $q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('wilaya', function (Builder $q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'company_name', 'type', 'research_difficulty', 'reliability_rating', 'quality_rating', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $suppliers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $suppliers,
            'message' => 'Suppliers retrieved successfully.'
        ]);
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        // Load relationships
        $supplier->load(['country', 'wilaya']);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier created successfully.'
        ], 201);
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load([
            'country',
            'wilaya',
            'supplierProducts.product',
            'products'
        ]);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier retrieved successfully.'
        ]);
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        // Load relationships
        $supplier->load(['country', 'wilaya']);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier updated successfully.'
        ]);
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully.'
        ]);
    }

    /**
     * Get suppliers for dropdown/select options.
     */
    public function options(Request $request): JsonResponse
    {
        $query = Supplier::select('id', 'name', 'company_name', 'type')
            ->active()
            ->researched();

        // Filter by type if requested
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by location if requested
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        if ($request->filled('wilaya_id')) {
            $query->where('wilaya_id', $request->input('wilaya_id'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->display_name,
                    'type' => $supplier->type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $suppliers,
            'message' => 'Supplier options retrieved successfully.'
        ]);
    }

    /**
     * Get countries for supplier location selection.
     */
    public function countries(): JsonResponse
    {
        $countries = Country::select('id', 'name', 'code')
            ->where('allows_imports', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $countries,
            'message' => 'Countries retrieved successfully.'
        ]);
    }

    /**
     * Get wilayas for supplier location selection.
     */
    public function wilayas(): JsonResponse
    {
        $wilayas = Wilaya::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wilayas,
            'message' => 'Wilayas retrieved successfully.'
        ]);
    }

    /**
     * Mark a supplier as researched.
     */
    public function markAsResearched(Supplier $supplier): JsonResponse
    {
        $supplier->update(['is_researched' => true]);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier marked as researched successfully.'
        ]);
    }

    /**
     * Get supplier statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::active()->count(),
            'researched_suppliers' => Supplier::researched()->count(),
            'international_suppliers' => Supplier::international()->count(),
            'local_suppliers' => Supplier::local()->count(),
            'reliable_suppliers' => Supplier::where('reliability_rating', '>=', 7)->count(),
            'high_quality_suppliers' => Supplier::where('quality_rating', '>=', 7)->count(),
            'suppliers_with_samples' => Supplier::where('provides_samples', true)->count(),
            'suppliers_accepting_small_orders' => Supplier::acceptsSmallOrders()->count(),
            'average_reliability_rating' => Supplier::avg('reliability_rating'),
            'average_quality_rating' => Supplier::avg('quality_rating'),
            'average_research_difficulty' => Supplier::avg('research_difficulty'),
            'average_response_time' => Supplier::avg('response_time_hours'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Supplier statistics retrieved successfully.'
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Research status filter
        if ($request->filled('is_researched')) {
            $query->where('is_researched', $request->boolean('is_researched'));
        }

        // Location filters
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        if ($request->filled('wilaya_id')) {
            $query->where('wilaya_id', $request->input('wilaya_id'));
        }

        // Rating filters
        if ($request->filled('reliability_min')) {
            $query->where('reliability_rating', '>=', $request->input('reliability_min'));
        }

        if ($request->filled('reliability_max')) {
            $query->where('reliability_rating', '<=', $request->input('reliability_max'));
        }

        if ($request->filled('quality_min')) {
            $query->where('quality_rating', '>=', $request->input('quality_min'));
        }

        if ($request->filled('quality_max')) {
            $query->where('quality_rating', '<=', $request->input('quality_max'));
        }

        // Research difficulty filters
        if ($request->filled('research_difficulty_min')) {
            $query->where('research_difficulty', '>=', $request->input('research_difficulty_min'));
        }

        if ($request->filled('research_difficulty_max')) {
            $query->where('research_difficulty', '<=', $request->input('research_difficulty_max'));
        }

        // Response time filters
        if ($request->filled('response_time_max')) {
            $query->where('response_time_hours', '<=', $request->input('response_time_max'));
        }

        // Boolean filters
        if ($request->filled('accepts_small_orders')) {
            $query->where('accepts_small_orders', $request->boolean('accepts_small_orders'));
        }

        if ($request->filled('provides_samples')) {
            $query->where('provides_samples', $request->boolean('provides_samples'));
        }

        // Date filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }
    }
} 