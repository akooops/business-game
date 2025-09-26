<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $product = request()->route('product');

        return [
            'name' => 'required|string|max:255|unique:products,name,'.$product->id,
            'description' => 'nullable|string',
            'type' => 'required|in:raw_material,component,finished_product',
            'elasticity_coefficient' => 'required|numeric',
            'storage_cost' => 'required|numeric|min:0', 
            'has_expiration' => 'required|boolean',
            'shelf_life_days' => 'required_if:has_expiration,true|integer|min:1',
            'needs_technology' => 'required|boolean',
            'technology_id' => 'required_if:needs_technology,true|exists:technologies,id',
            'file' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
} 