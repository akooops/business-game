<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:international,local'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'wilaya_id' => ['nullable', 'exists:wilayas,id'],
            'research_difficulty' => ['required', 'integer', 'min:1', 'max:10'],
            'research_time_days' => ['required', 'integer', 'min:1'],
            'is_researched' => ['boolean'],
            'reliability_rating' => ['required', 'numeric', 'min:1', 'max:10'],
            'quality_rating' => ['required', 'numeric', 'min:1', 'max:10'],
            'response_time_hours' => ['required', 'integer', 'min:1'],
            'payment_terms_days' => ['required', 'numeric', 'min:0'],
            'accepts_small_orders' => ['boolean'],
            'provides_samples' => ['boolean'],
            'specialties' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:active,inactive,blacklisted'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The supplier name is required.',
            'type.required' => 'The supplier type is required.',
            'type.in' => 'The supplier type must be either international or local.',
            'country_id.exists' => 'The selected country does not exist.',
            'wilaya_id.exists' => 'The selected wilaya does not exist.',
            'research_difficulty.required' => 'The research difficulty is required.',
            'research_difficulty.min' => 'The research difficulty must be at least 1.',
            'research_difficulty.max' => 'The research difficulty must not exceed 10.',
            'research_time_days.required' => 'The research time is required.',
            'research_time_days.min' => 'The research time must be at least 1 day.',
            'reliability_rating.required' => 'The reliability rating is required.',
            'reliability_rating.min' => 'The reliability rating must be at least 1.',
            'reliability_rating.max' => 'The reliability rating must not exceed 10.',
            'quality_rating.required' => 'The quality rating is required.',
            'quality_rating.min' => 'The quality rating must be at least 1.',
            'quality_rating.max' => 'The quality rating must not exceed 10.',
            'response_time_hours.required' => 'The response time is required.',
            'response_time_hours.min' => 'The response time must be at least 1 hour.',
            'payment_terms_days.required' => 'The payment terms are required.',
            'payment_terms_days.min' => 'The payment terms must be at least 0 days.',
            'email.email' => 'The email must be a valid email address.',
            'website.url' => 'The website must be a valid URL.',
            'status.required' => 'The supplier status is required.',
            'status.in' => 'The supplier status must be active, inactive, or blacklisted.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $countryId = $this->input('country_id');
            $wilayaId = $this->input('wilaya_id');

            // Validate location based on type
            if ($type === 'international') {
                if (empty($countryId)) {
                    $validator->errors()->add('country_id', 'A country is required for international suppliers.');
                }
                if (!empty($wilayaId)) {
                    $validator->errors()->add('wilaya_id', 'Wilaya should not be specified for international suppliers.');
                }
            } elseif ($type === 'local') {
                if (empty($wilayaId)) {
                    $validator->errors()->add('wilaya_id', 'A wilaya is required for local suppliers.');
                }
                if (!empty($countryId)) {
                    $validator->errors()->add('country_id', 'Country should not be specified for local suppliers.');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string 'true'/'false' to boolean for boolean fields
        if ($this->has('is_researched')) {
            $this->merge(['is_researched' => filter_var($this->input('is_researched'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('accepts_small_orders')) {
            $this->merge(['accepts_small_orders' => filter_var($this->input('accepts_small_orders'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('provides_samples')) {
            $this->merge(['provides_samples' => filter_var($this->input('provides_samples'), FILTER_VALIDATE_BOOLEAN)]);
        }
    }
} 