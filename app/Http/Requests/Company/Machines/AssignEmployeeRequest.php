<?php

namespace App\Http\Requests\Company\Machines;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\ProductionService;

class AssignEmployeeRequest extends FormRequest
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
            'employee_id' => 'required|exists:employees,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $companyMachine = $this->route('companyMachine');

            $employee = $this->input('employee_id');
            $employee = Employee::find($employee);

            if(!$employee){
                $validator->errors()->add('employee', 'Employee not found.');
                return;
            }

            $errors = ProductionService::validateAssignEmployee($companyMachine, $employee);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
