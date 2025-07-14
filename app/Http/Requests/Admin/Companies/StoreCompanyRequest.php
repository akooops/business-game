<?php

namespace App\Http\Requests\Admin\Companies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
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
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|max:255|email|unique:users,email',
            'password' => 'required|string|min:6|max:255',

            'funds' => 'required|numeric|min:0',
            'carbon_footprint' => 'required|numeric|min:0',
            'research_level' => 'required|integer|min:0',

            'file' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
}
