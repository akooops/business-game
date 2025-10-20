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
            'quantity' => ['required', 'numeric', 'min:0.000001', 'max:999999999.999999'],
        ];
    }
} 