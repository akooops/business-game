<?php

namespace App\Http\Requests\Admin\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic supplier information
            'name' => 'required|string|max:255',
            'is_international' => 'boolean',
            
            // Shipping costs
            'min_shipping_cost' => 'required|numeric|min:0',
            'max_shipping_cost' => 'required|numeric|min:0|gte:min_shipping_cost',
            
            // Shipping times
            'min_shipping_time_days' => 'required|integer|min:1',
            'max_shipping_time_days' => 'required|integer|min:1|gte:min_shipping_time_days',
            
            // Environmental impact
            'carbon_footprint' => 'required|numeric|min:0',
            
            // Location relationships
            'country_id' => 'required_if:is_international,true|exists:countries,id',
            'wilaya_id' => 'required_if:is_international,false|exists:wilayas,id',
            
            // Products array
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.min_sale_price' => 'required|numeric|min:0|gte:0',
            'products.*.max_sale_price' => 'required|numeric|min:0|gte:products.*.min_sale_price',

            'file' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
} 