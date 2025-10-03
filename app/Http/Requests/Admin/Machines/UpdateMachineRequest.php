<?php

namespace App\Http\Requests\Admin\Machines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
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
            // Basic machine details
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machines', 'name')->ignore($this->route('machine'))
            ],
            'model' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_to_acquire' => 'required|numeric|min:0',
            'loss_on_sale_days' => 'required|numeric|min:0',

            // Performance metrics
            'operations_cost' => 'required|numeric|min:0',
            'carbon_footprint' => 'required|numeric|min:0',
            'quality_factor' => 'required|numeric|min:0',
            
            // Speed ranges
            'min_speed' => 'required|numeric|min:0',
            'max_speed' => 'required|numeric|min:0|gte:min_speed',
            
            // Reliability
            'reliability_decay_days' => 'required|numeric|min:0|max:1',
            
            // Maintenance
            'min_maintenance_cost' => 'required|numeric|min:0',
            'max_maintenance_cost' => 'required|numeric|min:0|gte:min_maintenance_cost',
            'min_maintenance_time_days' => 'required|integer|min:0',
            'max_maintenance_time_days' => 'required|integer|min:0|gte:min_maintenance_time_days',
            
            // Machine employee profiles validation
            'employee_profile_id' => 'required|exists:employee_profiles,id',
            
            // Machine outputs validation
            'outputs' => 'nullable|array',
            'outputs.*.product_id' => 'required|exists:products,id',

            // Image file
            'file' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
} 