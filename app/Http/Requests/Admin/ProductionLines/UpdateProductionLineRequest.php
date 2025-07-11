<?php

namespace App\Http\Requests\Admin\ProductionLines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductionLineRequest extends FormRequest
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
        $productionLine = request()->route('productionLine') ?? request()->route('production_line');

        return [
            'name' => 'required|string|max:255|unique:production_lines,name,'.$productionLine->id,
            'description' => 'nullable|string',
            'area_required' => 'required|numeric|min:0|max:999999.999',
            
            // Production line outputs validation
            'outputs' => 'nullable|array',
            'outputs.*.product_id' => 'required|exists:products,id',
            
            // Production line steps validation
            'steps' => 'nullable|array',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.description' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'outputs.*.product_id.required' => 'Product is required for each output.',
            'outputs.*.product_id.exists' => 'Selected product does not exist.',
            'steps.*.name.required' => 'Step name is required.',
            'steps.*.step.required' => 'Step order is required.',
            'steps.*.step.integer' => 'Step order must be a number.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'area_required' => 'area required',
            'outputs.*.product_id' => 'product',
            'steps.*.name' => 'step name',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure arrays are properly formatted if they come as JSON strings
        if ($this->has('outputs') && is_string($this->outputs)) {
            $this->merge([
                'outputs' => json_decode($this->outputs, true)
            ]);
        }

        if ($this->has('steps') && is_string($this->steps)) {
            $this->merge([
                'steps' => json_decode($this->steps, true)
            ]);
        }
    }
} 