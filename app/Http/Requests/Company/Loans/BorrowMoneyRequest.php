<?php

namespace App\Http\Requests\Company\Loans;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\ValidationService;

class BorrowMoneyRequest extends FormRequest
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
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0.001',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $bank = Bank::find($this->input('bank_id'));
            $amount = $this->input('amount');

            $errors = ValidationService::validateBorrowMoney($bank, $amount);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
