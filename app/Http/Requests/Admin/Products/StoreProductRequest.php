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
            'elasticity_coefficient' => 'required|numeric',
            'avg_demand' => 'required|numeric|min:0', 
            'avg_market_price' => 'required|numeric|min:0',
            'is_saleable' => 'required|boolean',
            'has_expiration' => 'required|boolean',
            'shelf_life_days' => 'required_if:has_expiration,true|integer|min:1',
            'needs_technology' => 'required|boolean',
            'technology_id' => 'required_if:needs_technology,true|exists:technologies,id',
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }
} 