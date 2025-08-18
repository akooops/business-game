<?php

namespace App\Http\Requests\Company\ProductionOrders;

use App\Models\Product;
use App\Services\ValidationService;
use Illuminate\Foundation\Http\FormRequest;

class CancelProductionOrderRequest extends FormRequest
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
            $productionOrder = $this->productionOrder;

            $errors = ValidationService::validateCancelProductionOrder($company, $productionOrder);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
