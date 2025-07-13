<?php

namespace App\Http\Requests\Admin\ProductDemand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductDemandRequest extends FormRequest
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
            'min_demand' => 'required|numeric|min:0',
            'max_demand' => 'required|numeric|min:0|gte:min_demand',
            'avg_demand' => 'required|numeric|min:0|gte:min_demand|lte:max_demand',
            'market_price' => 'required|numeric|min:0',
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
            'gameweek' => 'gameweek',
            'min_demand' => 'minimum demand',
            'max_demand' => 'maximum demand',
            'avg_demand' => 'average demand',
            'market_price' => 'market price',
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
            'gameweek.unique' => 'Gameweek :input already exists for this product.',
            'max_demand.gte' => 'Maximum demand must be greater than or equal to minimum demand.',
            'avg_demand.gte' => 'Average demand must be greater than or equal to minimum demand.',
            'avg_demand.lte' => 'Average demand must be less than or equal to maximum demand.',
        ];
    }
} 