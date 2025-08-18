<?php

namespace App\Http\Requests\Admin\EmployeeProfiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeProfileRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:employee_profiles,name',
            'description' => 'nullable|string',
            
            // Salary validation - min must be less than avg, avg must be less than max
            'min_salary_month' => 'required|numeric|min:0',
            'max_salary_month' => 'required|numeric|min:0|gte:min_salary_month',

            // Recruitment
            'min_recruitment_cost' => 'required|numeric|min:0',
            'max_recruitment_cost' => 'required|numeric|min:0|gte:min_recruitment_cost',            
        ];
    }
} 