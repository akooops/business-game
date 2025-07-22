<?php

namespace App\Http\Requests\Company\Machines;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\ProductionService;
use App\Services\MaintenanceService;

class StartMaintenanceRequest extends FormRequest
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
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $companyMachine = $this->route('companyMachine');

            $errors = MaintenanceService::validateMaintenance($companyMachine);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
