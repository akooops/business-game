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
        
            // Tax rates (percentages as decimals, 0-1 range)
            'customs_duties_rate' => 'required|numeric|min:0|max:1',
        
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
            'customs_duties_rate' => 'customs duties rate',
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
            'customs_duties_rate.max' => 'Customs duties rate must be between 0 and 1 (0-100%).',
            'file.required' => 'Flag image is required.',
            'file.max' => 'Flag image must not exceed 2MB.',
        ];
    }
} 