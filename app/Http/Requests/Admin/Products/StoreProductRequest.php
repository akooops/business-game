<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'type' => 'required|in:raw_material,component,finished_product',
            'elasticity_coefficient' => 'required|numeric|min:-1|max:1',
            'has_expiration' => 'required|boolean',
            'shelf_life_days' => 'required_if:has_expiration,true|integer|min:1',
            'measurement_unit' => 'required|string|max:255',
            'need_technology' => 'required|boolean',
            'technology_id' => 'required_if:need_technology,true|exists:technologies,id',
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'elasticity_coefficient' => 'elasticity coefficient',
            'shelf_life_days' => 'shelf life (days)',
            'has_expiration' => 'has expiration',
            'measurement_unit' => 'measurement unit',
            'need_technology' => 'need technology',
            'technology_id' => 'technology',
        ];
    }
} 