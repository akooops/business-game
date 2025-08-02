<?php

namespace App\Http\Requests\Admin\Events;

use Illuminate\Foundation\Http\FormRequest;

class RunEventRequest extends FormRequest
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
            'event' => 'required|in:allow-countries-import,block-countries-import,close-suez-canal,open-suez-canal,damage-inventory-product,lower-customs-duties-rate,lower-oil-price,raise-customs-duties-rate,raise-oil-price',
        ];
    }
} 