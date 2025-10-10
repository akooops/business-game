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
            'event' => 'required|in:allow-countries-import,block-countries-import,open-suez-canal,close-suez-canal,open-ormuz-canal,close-ormuz-canal,start-heat-wave,stop-heat-wave,start-health-complaint,stop-health-complaint,start-workers-protest,end-workers-protest,start-work-accident',
        ];
    }
} 