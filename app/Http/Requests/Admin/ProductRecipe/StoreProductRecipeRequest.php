<?php

namespace App\Http\Requests\Admin\ProductRecipe;

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
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'material_id' => [
                'required',
                'integer',
                'exists:products,id',
                'different:product_id',
                Rule::unique('product_recipes')
                    ->where('product_id', $this->product_id)
                    ->where('material_id', $this->material_id)
            ],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'material_id.required' => 'Material is required.',
            'material_id.exists' => 'Selected material does not exist.',
            'material_id.different' => 'A product cannot be a material of itself.',
            'material_id.unique' => 'This material is already added to the recipe.',
            'quantity.required' => 'Quantity is required.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 0.01.',
        ];
    }
} 