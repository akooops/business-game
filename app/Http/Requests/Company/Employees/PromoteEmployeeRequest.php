<?php

namespace App\Http\Requests\Company\Employees;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\HrService;

class PromoteEmployeeRequest extends FormRequest
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
        $employee = request()->route('employee');

        return [
            'new_salary' => 'required|numeric|min:' . $employee->salary_month,
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $employee = request()->route('employee');

            if (!$employee) {
                $validator->errors()->add('employee', 'The selected employee does not exist.');
                return;
            }

            $errors = HrService::validatePromotion($employee);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
