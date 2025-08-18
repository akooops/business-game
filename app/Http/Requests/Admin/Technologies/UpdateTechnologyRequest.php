<?php

namespace App\Http\Requests\Admin\Technologies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTechnologyRequest extends FormRequest
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
        $technology = request()->route('technology');

        return [
            'name' => 'required|string|max:255|unique:technologies,name,'.$technology->id,
            'description' => 'nullable|string',
            'level' => 'required|integer|min:0',
            'research_cost' => 'required|numeric|min:0',
            'research_time_days' => 'required|integer|min:0',
        ];
    }
} 