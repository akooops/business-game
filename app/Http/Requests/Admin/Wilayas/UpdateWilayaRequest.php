<?php

namespace App\Http\Requests\Admin\Wilayas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWilayaRequest extends FormRequest
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
        $wilaya = $this->route('wilaya');

        return [
            // Basic country details
            'name' => 'required|string|max:255|unique:wilayas,name,' . $wilaya->id,
            
            // Tax rates (percentages as decimals, 0-1 range)
            'min_shipping_cost' => 'required|numeric|min:0',
            'max_shipping_cost' => 'required|numeric|min:0',
            'avg_shipping_cost' => 'required|numeric|min:0',
            
            // Shipping time
            'min_shipping_time_days' => 'required|integer|min:0',
            'avg_shipping_time_days' => 'required|integer|min:0',
            'max_shipping_time_days' => 'required|integer|min:0',
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