<?php

namespace App\Http\Requests\Admin\Wilayas;

use Illuminate\Foundation\Http\FormRequest;

class StoreWilayaRequest extends FormRequest
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
            // Basic country details
            'name' => 'required|string|max:255|unique:wilayas,name',
            
            // Shipping costs
            'min_shipping_cost' => 'required|numeric|min:0',
            'max_shipping_cost' => 'required|numeric|min:0|gte:min_shipping_cost',
            'avg_shipping_cost' => 'required|numeric|min:0|gte:min_shipping_cost|lte:max_shipping_cost',
            
            // Shipping time
            'min_shipping_time_days' => 'required|integer|min:0',
            'avg_shipping_time_days' => 'required|integer|min:0|gte:min_shipping_time_days|lte:max_shipping_time_days',
            'max_shipping_time_days' => 'required|integer|min:0|gte:min_shipping_time_days',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'wilaya name',
            'min_shipping_cost' => 'minimum shipping cost',
            'max_shipping_cost' => 'maximum shipping cost',
            'avg_shipping_cost' => 'average shipping cost',
            'min_shipping_time_days' => 'minimum shipping time',
            'avg_shipping_time_days' => 'average shipping time',
            'max_shipping_time_days' => 'maximum shipping time',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A wilaya with this name already exists.',
            'min_shipping_cost.required' => 'Minimum shipping cost is required.',
            'max_shipping_cost.required' => 'Maximum shipping cost is required.',
            'avg_shipping_cost.required' => 'Average shipping cost is required.',
            'min_shipping_time_days.required' => 'Minimum shipping time is required.',
            'avg_shipping_time_days.required' => 'Average shipping time is required.',
            'max_shipping_time_days.required' => 'Maximum shipping time is required.',
        ];
    }
} 