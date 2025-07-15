<?php

namespace App\Http\Requests\Company\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\FinanceService;
use App\Services\ProcurementService;
use App\Models\Product;

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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $supplier = request()->route('supplier');
            $quantity = request()->input('quantity');

            $product = Product::find(request()->input('product_id'));

            if (!$product) {
                $validator->errors()->add('product_id', 'The selected product does not exist.');
                return;
            }

            $errors = ProcurementService::validatePurchase($company, $supplier, $product, $quantity);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
