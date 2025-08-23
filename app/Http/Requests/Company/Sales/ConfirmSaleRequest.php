<?php

namespace App\Http\Requests\Company\Sales;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\SalesService;
use App\Services\ValidationService;

class ConfirmSaleRequest extends FormRequest
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
            $sale = request()->route('sale');

            $errors = ValidationService::validateSaleConfirmation($company, $sale);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
