<?php

namespace App\Http\Requests\Admin\Advertisers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertiserRequest extends FormRequest
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
        $advertiser = request()->route('advertiser');

        return [
            'name' => 'required|string|max:255|unique:advertisers,name,'.$advertiser->id,
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0',
            'min_market_impact_percentage' => 'required|numeric|min:0',
            'max_market_impact_percentage' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:0',

            'file' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
}
