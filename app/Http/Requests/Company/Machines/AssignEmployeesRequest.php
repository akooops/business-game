<?php

namespace App\Http\Requests\Company\Machines;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\ProductionService;

class AssignEmployeesRequest extends FormRequest
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
            'employees' => 'required|array',
            'employees.*' => 'required|exists:employees,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $companyMachine = $this->route('companyMachine');
            $employees = $this->input('employees');

            $errors = ProductionService::validateAssignEmployees($company, $companyMachine, $employees);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
