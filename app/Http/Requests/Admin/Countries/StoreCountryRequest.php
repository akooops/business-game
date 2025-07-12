<?php

namespace App\Http\Requests\Admin\Countries;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:countries,name',
            'code' => 'required|string|max:3|unique:countries,code',
            
            // Tax rates (percentages as decimals, 0-1 range)
            'customs_duties_rate' => 'required|numeric|min:0|max:1',
            'tva_rate' => 'required|numeric|min:0|max:1',
            'insurance_rate' => 'required|numeric|min:0|max:1',
            
            // Costs
            'freight_cost' => 'required|numeric|min:0',
            'port_handling_fee' => 'required|numeric|min:0',
            
            // System flags
            'allows_imports' => 'required|boolean',
            
            // Flag image file
            'file' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'country name',
            'code' => 'country code',
            'customs_duties_rate' => 'customs duties rate',
            'tva_rate' => 'TVA rate',
            'insurance_rate' => 'insurance rate',
            'freight_cost' => 'freight cost',
            'port_handling_fee' => 'port handling fee',
            'allows_imports' => 'allows imports',
            'file' => 'flag image',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A country with this name already exists.',
            'code.unique' => 'A country with this code already exists.',
            'code.max' => 'Country code must be 3 characters or less.',
            'customs_duties_rate.max' => 'Customs duties rate must be between 0 and 1 (0-100%).',
            'tva_rate.max' => 'TVA rate must be between 0 and 1 (0-100%).',
            'insurance_rate.max' => 'Insurance rate must be between 0 and 1 (0-100%).',
            'file.required' => 'Flag image is required.',
            'file.max' => 'Flag image must not exceed 2MB.',
        ];
    }
} 