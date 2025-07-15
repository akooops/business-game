<?php

namespace App\Http\Requests\Admin\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
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
            'avg_shipping_cost' => 'required|numeric|min:0|gte:min_shipping_cost|lte:max_shipping_cost',
            
            // Shipping times
            'min_shipping_time_days' => 'required|integer|min:1',
            'avg_shipping_time_days' => 'required|integer|min:1|gte:min_shipping_time_days|lte:max_shipping_time_days',
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
            'products.*.avg_sale_price' => 'required|numeric|min:0|gte:products.*.min_sale_price|lte:products.*.max_sale_price',
            'products.*.max_sale_price' => 'required|numeric|min:0|gte:products.*.min_sale_price',
            'products.*.minimum_order_qty' => 'required|integer|min:1',

            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The supplier name is required.',
            'name.string' => 'The supplier name must be a string.',
            'name.max' => 'The supplier name must not exceed 255 characters.',
            
            'min_shipping_cost.required' => 'The minimum shipping cost is required.',
            'min_shipping_cost.numeric' => 'The minimum shipping cost must be a number.',
            'min_shipping_cost.min' => 'The minimum shipping cost must be at least 0.',
            
            'max_shipping_cost.required' => 'The maximum shipping cost is required.',
            'max_shipping_cost.numeric' => 'The maximum shipping cost must be a number.',
            'max_shipping_cost.min' => 'The maximum shipping cost must be at least 0.',
            
            'avg_shipping_cost.required' => 'The average shipping cost is required.',
            'avg_shipping_cost.numeric' => 'The average shipping cost must be a number.',
            'avg_shipping_cost.min' => 'The average shipping cost must be at least 0.',
            
            'min_shipping_time_days.required' => 'The minimum shipping time is required.',
            'min_shipping_time_days.integer' => 'The minimum shipping time must be an integer.',
            'min_shipping_time_days.min' => 'The minimum shipping time must be at least 1 day.',
            
            'avg_shipping_time_days.required' => 'The average shipping time is required.',
            'avg_shipping_time_days.integer' => 'The average shipping time must be an integer.',
            'avg_shipping_time_days.min' => 'The average shipping time must be at least 1 day.',
            
            'max_shipping_time_days.required' => 'The maximum shipping time is required.',
            'max_shipping_time_days.integer' => 'The maximum shipping time must be an integer.',
            'max_shipping_time_days.min' => 'The maximum shipping time must be at least 1 day.',
            
            'carbon_footprint.required' => 'The carbon footprint is required.',
            'carbon_footprint.numeric' => 'The carbon footprint must be a number.',
            'carbon_footprint.min' => 'The carbon footprint must be at least 0.',
            
            'country_id.exists' => 'The selected country does not exist.',
            'wilaya_id.exists' => 'The selected wilaya does not exist.',
            
            'products.required' => 'At least one product is required.',
            'products.array' => 'Products must be an array.',
            'products.min' => 'At least one product is required.',
            
            'products.*.product_id.required' => 'Product selection is required.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            
            'products.*.min_sale_price.required' => 'The minimum sale price is required.',
            'products.*.min_sale_price.numeric' => 'The minimum sale price must be a number.',
            'products.*.min_sale_price.min' => 'The minimum sale price must be at least 0.',
            
            'products.*.avg_sale_price.required' => 'The average sale price is required.',
            'products.*.avg_sale_price.numeric' => 'The average sale price must be a number.',
            'products.*.avg_sale_price.min' => 'The average sale price must be at least 0.',
            
            'products.*.max_sale_price.required' => 'The maximum sale price is required.',
            'products.*.max_sale_price.numeric' => 'The maximum sale price must be a number.',
            'products.*.max_sale_price.min' => 'The maximum sale price must be at least 0.',
            
            'products.*.minimum_order_qty.required' => 'The minimum order quantity is required.',
            'products.*.minimum_order_qty.integer' => 'The minimum order quantity must be an integer.',
            'products.*.minimum_order_qty.min' => 'The minimum order quantity must be at least 1.',
        ];
    }
} 