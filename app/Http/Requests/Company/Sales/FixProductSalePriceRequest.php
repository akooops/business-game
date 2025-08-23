<?php

namespace App\Http\Requests\Company\Sales;

use App\Services\ValidationService;
use Illuminate\Foundation\Http\FormRequest;

class FixProductSalePriceRequest extends FormRequest
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
            'sale_price' => 'required|numeric|min:0.001',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $product = request()->route('product');

            $errors = ValidationService::validateProductSalePriceChange($company, $product);

            if($errors){
                foreach($errors as $key => $error){
                    $validator->errors()->add($key, $error);
                }
                return;
            }
        });
    }
}
