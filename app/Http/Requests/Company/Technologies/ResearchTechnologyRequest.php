<?php

namespace App\Http\Requests\Company\Technologies;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\FinanceService;
use App\Services\ValidationService;

class ResearchTechnologyRequest extends FormRequest
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
            $technology = request()->route('technology');
            $company = $this->company;

            $errors = ValidationService::validateTechnologyResearch($company, $technology);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
