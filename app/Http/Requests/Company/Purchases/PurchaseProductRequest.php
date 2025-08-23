<?php

namespace App\Http\Requests\Company\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\ValidationService;

class PurchaseProductRequest extends FormRequest
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
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $supplier = Supplier::find(request()->input('supplier_id'));
            $product = Product::find(request()->input('product_id'));
            $quantity = request()->input('quantity');

            $errors = ValidationService::validatePurchase($company, $supplier, $product, $quantity);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
