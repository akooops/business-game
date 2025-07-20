<?php

namespace App\Http\Requests\Admin\Machines;

use Illuminate\Foundation\Http\FormRequest;

class StoreMachineRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:machines,name',
            'model' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'cost_to_acquire' => 'required|numeric|min:0',
            'setup_time_days' => 'required|integer|min:0',

            // Performance metrics
            'operation_cost' => 'required|numeric|min:0',
            'carbon_footprint' => 'required|numeric|min:0',
            'quality_factor' => 'required|numeric|min:0|max:1',
            
            // Speed ranges
            'min_speed' => 'required|numeric|min:0',
            'avg_speed' => 'required|numeric|min:0|gte:min_speed|lte:max_speed',
            'max_speed' => 'required|numeric|min:0|gte:min_speed',
            
            // Reliability
            'reliability_decay_days' => 'required|numeric|min:0|max:1',
            'maintenance_interval_days' => 'required|integer|min:1',
            
            // Predictive maintenance PERT
            'min_predictive_maintenance_cost' => 'required|numeric|min:0',
            'avg_predictive_maintenance_cost' => 'required|numeric|min:0|gte:min_predictive_maintenance_cost|lte:max_predictive_maintenance_cost',
            'max_predictive_maintenance_cost' => 'required|numeric|min:0|gte:min_predictive_maintenance_cost',
            'min_predictive_maintenance_time_days' => 'required|integer|min:0',
            'avg_predictive_maintenance_time_days' => 'required|integer|min:0|gte:min_predictive_maintenance_time_days|lte:max_predictive_maintenance_time_days',
            'max_predictive_maintenance_time_days' => 'required|integer|min:0|gte:min_predictive_maintenance_time_days',
            
            // Corrective maintenance PERT
            'min_corrective_maintenance_cost' => 'required|numeric|min:0',
            'avg_corrective_maintenance_cost' => 'required|numeric|min:0|gte:min_corrective_maintenance_cost|lte:max_corrective_maintenance_cost',
            'max_corrective_maintenance_cost' => 'required|numeric|min:0|gte:min_corrective_maintenance_cost',
            'min_corrective_maintenance_time_days' => 'required|integer|min:0',
            'avg_corrective_maintenance_time_days' => 'required|integer|min:0|gte:min_corrective_maintenance_time_days|lte:max_corrective_maintenance_time_days',
            'max_corrective_maintenance_time_days' => 'required|integer|min:0|gte:min_corrective_maintenance_time_days',
            
            // Relationships
            'employee_profiles' => 'nullable|array',
            'employee_profiles.*.employee_profile_id' => 'required_with:employee_profiles|exists:employee_profiles,id',
            'employee_profiles.*.required_count' => 'required_with:employee_profiles|integer|min:1',
            
            // Machine outputs validation
            'outputs' => 'nullable|array',
            'outputs.*.product_id' => 'required|exists:products,id',
            
            // Image file
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'machine name',
            'model' => 'machine model',
            'manufacturer' => 'manufacturer',
            'cost_to_acquire' => 'acquisition cost',
            'operation_cost' => 'operation cost',
            'carbon_footprint' => 'carbon footprint',
            'quality_factor' => 'quality factor',
            'min_speed' => 'minimum speed',
            'avg_speed' => 'average speed',
            'max_speed' => 'maximum speed',
            'reliability_decay_days' => 'reliability decay',
            'maintenance_interval_days' => 'maintenance interval',
            'outputs.*.product_id' => 'product',
            'file' => 'machine image',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A machine with this name already exists.',
            'reliability_decay_days.max' => 'Reliability decay must be between 0 and 1.',
            'employee_profiles.*.employee_profile_id.exists' => 'Selected employee profile does not exist.',
            'outputs.*.product_id.required' => 'Product is required for each output.',
            'outputs.*.product_id.exists' => 'Selected product does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure arrays are properly formatted if they come as JSON strings
        if ($this->has('outputs') && is_string($this->outputs)) {
            $this->merge([
                'outputs' => json_decode($this->outputs, true)
            ]);
        }

        if ($this->has('employee_profiles') && is_string($this->employee_profiles)) {
            $this->merge([
                'employee_profiles' => json_decode($this->employee_profiles, true)
            ]);
        }
    }
} 