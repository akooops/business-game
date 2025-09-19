<?php

namespace App\Http\Requests\Company\Ads;

use App\Models\Advertiser;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\ValidationService;

class CreateAdRequest extends FormRequest
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
            'advertiser_id' => 'required|exists:advertisers,id',
            'product_id' => 'required|exists:products,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = $this->company;
            $advertiser = Advertiser::find($this->input('advertiser_id'));

            $errors = ValidationService::validateAdPackagePurchase($company, $advertiser);

            if($errors) {
                foreach($errors as $key => $error) {
                    $validator->errors()->add($key, $error);
                }
            }
        });
    }
}
