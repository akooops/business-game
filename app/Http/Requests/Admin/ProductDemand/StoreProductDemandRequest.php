<?php

namespace App\Http\Requests\Admin\ProductDemand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductDemandRequest extends FormRequest
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
            'min_demand' => 'required|numeric|min:0',
            'max_demand' => 'required|numeric|min:0|gte:min_demand',
            'avg_demand' => 'required|numeric|min:0|gte:min_demand|lte:max_demand',
            'market_price' => 'required|numeric|min:0',
            'visibility_cost' => 'required|numeric|min:0',
            'research_time_days' => 'required|integer|min:0',
            'fluctuation_factor' => 'required|numeric|min:0|max:10',
            'is_visible' => 'required|boolean',
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
            'gameweek' => 'gameweek',
            'min_demand' => 'minimum demand',
            'max_demand' => 'maximum demand',
            'avg_demand' => 'average demand',
            'market_price' => 'market price',
            'visibility_cost' => 'visibility cost',
            'research_time_days' => 'research time (days)',
            'fluctuation_factor' => 'fluctuation factor',
            'is_visible' => 'visibility status',
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
            'product_id.exists' => 'The selected product does not exist.',
        ];
    }
} 