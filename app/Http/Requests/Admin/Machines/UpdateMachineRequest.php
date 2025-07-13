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
            'cost_to_acquire' => 'required|numeric|min:0',
            'area_required' => 'required|numeric|min:0',
            'setup_time_days' => 'required|integer|min:0',

            // Performance metrics
            'energy_consumption_hour' => 'required|numeric|min:0',
            'carbon_emissions_hour' => 'required|numeric|min:0',
            'quality_factor' => 'required|numeric|min:0',
            
            // Speed ranges
            'min_speed_hour' => 'required|numeric|min:0',
            'avg_speed_hour' => 'required|numeric|min:0|gte:min_speed_hour|lte:max_speed_hour',
            'max_speed_hour' => 'required|numeric|min:0|gte:min_speed_hour',
            
            // Reliability
            'failure_chance_hour' => 'required|numeric|min:0|max:1',
            'reliability_decay_hour' => 'required|numeric|min:0|max:1',
            'maintenance_interval_days' => 'required|integer|min:1',
            
            // Predictive maintenance PERT
            'min_predictive_maintenance_cost' => 'required|numeric|min:0',
            'avg_predictive_maintenance_cost' => 'required|numeric|min:0|gte:min_predictive_maintenance_cost|lte:max_predictive_maintenance_cost',
            'max_predictive_maintenance_cost' => 'required|numeric|min:0|gte:min_predictive_maintenance_cost',
            'min_predictive_maintenance_time_hours' => 'required|integer|min:0',
            'avg_predictive_maintenance_time_hours' => 'required|integer|min:0|gte:min_predictive_maintenance_time_hours|lte:max_predictive_maintenance_time_hours',
            'max_predictive_maintenance_time_hours' => 'required|integer|min:0|gte:min_predictive_maintenance_time_hours',
            
            // Corrective maintenance PERT
            'min_corrective_maintenance_cost' => 'required|numeric|min:0',
            'avg_corrective_maintenance_cost' => 'required|numeric|min:0|gte:min_corrective_maintenance_cost|lte:max_corrective_maintenance_cost',
            'max_corrective_maintenance_cost' => 'required|numeric|min:0|gte:min_corrective_maintenance_cost',
            'min_corrective_maintenance_time_hours' => 'required|integer|min:0',
            'avg_corrective_maintenance_time_hours' => 'required|integer|min:0|gte:min_corrective_maintenance_time_hours|lte:max_corrective_maintenance_time_hours',
            'max_corrective_maintenance_time_hours' => 'required|integer|min:0|gte:min_corrective_maintenance_time_hours',
            
            // Machine employee profiles validation
            'employee_profiles' => 'nullable|array',
            'employee_profiles.*.employee_profile_id' => 'required_with:employee_profiles|exists:employee_profiles,id',
            'employee_profiles.*.required_count' => 'required_with:employee_profiles|integer|min:1',
            
            // Machine outputs validation
            'outputs' => 'nullable|array',
            'outputs.*.product_id' => 'required|exists:products,id',

            // Image file
            'file' => 'nullable|file|mimes:jpg,jpeg,png',
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
            'energy_consumption_hour' => 'energy consumption',
            'carbon_emissions_hour' => 'carbon emissions',
            'quality_factor' => 'quality factor',
            'min_speed_hour' => 'minimum speed',
            'avg_speed_hour' => 'average speed',
            'max_speed_hour' => 'maximum speed',
            'failure_chance_hour' => 'failure chance',
            'reliability_decay_hour' => 'reliability decay',
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
            'failure_chance_hour.max' => 'Failure chance must be between 0 and 1.',
            'reliability_decay_hour.max' => 'Reliability decay must be between 0 and 1.',
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