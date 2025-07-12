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
            'hourly_energy_consumption' => 'required|numeric|min:0',
            'hourly_carbon_emissions' => 'required|numeric|min:0',
            'quality_factor' => 'required|numeric|min:0',
            
            // Speed ranges
            'hourly_speed_min' => 'required|numeric|min:0',
            'hourly_speed_avg' => 'required|numeric|min:0',
            'hourly_speed_max' => 'required|numeric|min:0',
            
            // Reliability
            'hourly_failure_chance' => 'required|numeric|min:0|max:1',
            'hourly_reliability_decay' => 'required|numeric|min:0|max:1',
            'maintenance_interval_days' => 'required|integer|min:1',
            
            // Predictive maintenance PERT
            'predictive_maintenance_cost_min' => 'required|numeric|min:0',
            'predictive_maintenance_cost_avg' => 'required|numeric|min:0',
            'predictive_maintenance_cost_max' => 'required|numeric|min:0',
            'predictive_maintenance_delay_min_hours' => 'required|integer|min:0',
            'predictive_maintenance_delay_avg_hours' => 'required|integer|min:0',
            'predictive_maintenance_delay_max_hours' => 'required|integer|min:0',
            
            // Corrective maintenance PERT
            'corrective_maintenance_cost_min' => 'required|numeric|min:0',
            'corrective_maintenance_cost_avg' => 'required|numeric|min:0',
            'corrective_maintenance_cost_max' => 'required|numeric|min:0',
            'corrective_maintenance_delay_min_hours' => 'required|integer|min:0',
            'corrective_maintenance_delay_avg_hours' => 'required|integer|min:0',
            'corrective_maintenance_delay_max_hours' => 'required|integer|min:0',
            
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
            'hourly_energy_consumption' => 'energy consumption',
            'hourly_carbon_emissions' => 'carbon emissions',
            'quality_factor' => 'quality factor',
            'hourly_speed_min' => 'minimum speed',
            'hourly_speed_avg' => 'average speed',
            'hourly_speed_max' => 'maximum speed',
            'hourly_failure_chance' => 'failure chance',
            'hourly_reliability_decay' => 'reliability decay',
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
            'hourly_failure_chance.max' => 'Failure chance must be between 0 and 1.',
            'hourly_reliability_decay.max' => 'Reliability decay must be between 0 and 1.',
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