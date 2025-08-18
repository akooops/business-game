<?php

namespace App\Http\Requests\Admin\Wilayas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWilayaRequest extends FormRequest
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
        $wilaya = $this->route('wilaya');

        return [
            // Basic country details
            'name' => 'required|string|max:255|unique:wilayas,name,' . $wilaya->id,
            
            // Shipping costs
            'min_shipping_cost' => 'required|numeric|min:0',
            'max_shipping_cost' => 'required|numeric|min:0|gte:min_shipping_cost',
            
            // Shipping time
            'min_shipping_time_days' => 'required|integer|min:0',
            'max_shipping_time_days' => 'required|integer|min:0|gte:min_shipping_time_days',
        ];
    }
} 