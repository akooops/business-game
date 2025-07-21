<?php

namespace App\Http\Requests\Company\ProductionOrders;

use App\Models\Product;
use App\Services\ProductionService;
use Illuminate\Foundation\Http\FormRequest;

class ProduceProductRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $companyMachine = $this->companyMachine;
            $quantity = $this->quantity;

            $product = Product::find($this->product_id);

            if(!$product){
                $validator->errors()->add('product_id', 'Product not found.');
                return;
            }

            $errors = ProductionService::validateProductionOrder($companyMachine, $product, $quantity);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
