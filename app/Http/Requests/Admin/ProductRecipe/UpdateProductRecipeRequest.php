<?php

namespace App\Http\Requests\Admin\ProductRecipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRecipeRequest extends FormRequest
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
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Quantity is required.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 0.01.',
        ];
    }
} 