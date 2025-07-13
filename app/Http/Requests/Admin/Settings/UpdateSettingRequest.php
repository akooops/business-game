<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function rules()
    {
        $setting = request()->route('setting');
        $type = $setting->type;
        $options = $setting->options;

        $baseRule = ['required'];

        $typeRules = match($type) {
            'text' => ['string'],
            'number' => ['numeric'],
            'select' => $options && is_array($options) ? ['string', 'in:' . implode(',', $options)] : ['string'],
            'array' => ['string'], // Arrays come as JSON string from frontend
            default => ['string'],
        };

        return [
            'value' => array_merge($baseRule, $typeRules)
        ];
    }
}