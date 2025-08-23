<?php

namespace App\Http\Requests\Admin\Countries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('countries', 'name')->ignore($this->route('country'))
            ],

            
            // Tax rates (percentages as decimals, 0-1 range)
            'customs_duties_rate' => 'required|numeric|min:0|max:1',
            
            // System flags
            'allows_imports' => 'required|boolean',
        ];
    }
} 