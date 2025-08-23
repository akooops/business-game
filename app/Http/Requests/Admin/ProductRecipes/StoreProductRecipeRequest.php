<?php

namespace App\Http\Requests\Admin\ProductRecipes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRecipeRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'material_id' => [
                'required',
                'exists:products,id',
                'different:product_id',
                Rule::unique('product_recipes', 'material_id')
                    ->where('product_id', $this->input('product_id'))
            ],
            'quantity' => 'required|numeric|min:0.01'        
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
            'product_id' => 'product',
            'material_id' => 'material',
            'quantity' => 'quantity',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'material_id.unique' => 'This material is already in the recipe.',
            'material_id.different' => 'A product cannot be a material of itself.',
            'quantity.min' => 'Quantity must be greater than 0.',
        ];
    }
} 