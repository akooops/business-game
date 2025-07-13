<?php

namespace App\Http\Requests\Admin\EmployeeProfiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeProfileRequest extends FormRequest
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
        $employeeProfile = request()->route('employeeProfile') ?? request()->route('employee_profile');

        return [
            'name' => 'required|string|max:255|unique:employee_profiles,name,'.$employeeProfile->id,
            'description' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:255',
            
            // Salary validation - min must be less than avg, avg must be less than max
            'min_salary_month' => 'required|numeric|min:0',
            'avg_salary_month' => 'required|numeric|min:0|gte:min_salary_month|lte:max_salary_month',
            'max_salary_month' => 'required|numeric|min:0|gte:min_salary_month',
            
            // Recruitment
            'recruitment_difficulty' => 'required|in:very_easy,easy,medium,hard,very_hard',
            'recruitment_cost_per_employee' => 'required|numeric|min:0',
            
            // Training
            'training_cost_per_employee' => 'required|numeric|min:0',
            'training_duration_days' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'avg_salary_month.gte' => 'Average salary must be greater than or equal to minimum salary.',
            'max_salary_month.gte' => 'Maximum salary must be greater than or equal to average salary.',
            'recruitment_difficulty.in' => 'Please select a valid recruitment difficulty level.',
            'training_duration_days.max' => 'Training duration cannot exceed 365 days.',
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
            'min_salary_month' => 'minimum monthly salary',
            'avg_salary_month' => 'average monthly salary',
            'max_salary_month' => 'maximum monthly salary',
            'recruitment_difficulty' => 'recruitment difficulty',
            'recruitment_cost_per_employee' => 'recruitment cost per employee',
            'training_cost_per_employee' => 'training cost per employee',
            'training_duration_days' => 'training duration (days)',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure skills array is properly formatted if it comes as JSON string
        if ($this->has('skills') && is_string($this->skills)) {
            $this->merge([
                'skills' => json_decode($this->skills, true)
            ]);
        }
    }
} 