<?php

namespace App\Http\Requests\Admin\Banks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:banks,name',
            'loan_duration_months' => 'required|integer|min:1',
            'loan_interest_rate' => 'required|numeric|min:0|max:1',
            'loan_max_amount' => 'required|numeric|min:0',

            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }
}
