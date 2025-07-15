<?php

namespace App\Http\Requests\Company\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\ProcurementService;
use App\Models\Supplier;

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
            'quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $product = request()->route('product');
            $quantity = request()->input('quantity');

            $supplier = Supplier::find(request()->input('supplier_id'));

            if (!$product) {
                $validator->errors()->add('supplier_id', 'The selected supplier does not exist.');
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
