<?php

namespace App\Http\Requests\Admin\EmployeeProfiles;

use Illuminate\Foundation\Http\FormRequest;

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
        $employeeProfile = request()->route('employeeProfile');

        return [
            'name' => 'required|string|max:255|unique:employee_profiles,name,'.$employeeProfile->id,
            'description' => 'nullable|string',
            
            // Salary validation - min must be less than avg, avg must be less than max
            'min_salary_month' => 'required|numeric|min:0',
            'avg_salary_month' => 'required|numeric|min:0|gte:min_salary_month|lte:max_salary_month',
            'max_salary_month' => 'required|numeric|min:0|gte:min_salary_month',
            
            // Recruitment
            'min_recruitment_cost' => 'required|numeric|min:0',
            'avg_recruitment_cost' => 'required|numeric|min:0|gte:min_recruitment_cost|lte:max_recruitment_cost',
            'max_recruitment_cost' => 'required|numeric|min:0|gte:min_recruitment_cost',
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
            'min_recruitment_cost' => 'minimum recruitment cost',
            'avg_recruitment_cost' => 'average recruitment cost',
            'max_recruitment_cost' => 'maximum recruitment cost',
        ];
    }
} 