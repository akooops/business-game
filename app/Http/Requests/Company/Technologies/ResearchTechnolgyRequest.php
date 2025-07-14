<?php

namespace App\Http\Requests\Company\Technologies;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\FinanceService;

class ResearchTechnolgyRequest extends FormRequest
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

            // Check if company has sufficient funds
            if (!FinanceService::haveSufficientFunds($company, $technology->research_cost)) {
                $validator->errors()->add('funds', 'You do not have enough funds to research this technology. Required: DZD ' . $technology->research_cost . ', Available: DZD ' . $company->funds);
            }
            
            // Check if technology exists
            if (!$technology) {
                $validator->errors()->add('technology_id', 'The selected technology does not exist.');
                return;
            }

            // Check if company is already researching this technology
            $alreadyResearching = $company->companyTechnologies()
                ->where('technology_id', $technology->id)
                ->exists();
            
            if ($alreadyResearching) {
                $validator->errors()->add('technology_id', 'You are already researching this technology.');
                return;
            }

            // Check research level
            if ($technology->level > $company->research_level + 1) {
                $validator->errors()->add('technology_id', 'You can only research technologies up to level ' . ($company->research_level + 1) . '.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'funds.max' => 'You do not have enough funds to research this technology.',
            'technology_id.unique' => 'You are already researching this technology.',
            'technology_id.exists' => 'The selected technology does not exist.',
        ];
    }
}
