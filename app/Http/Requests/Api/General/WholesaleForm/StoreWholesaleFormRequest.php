<?php

namespace App\Http\Requests\Api\General\WholesaleForm;

use Illuminate\Foundation\Http\FormRequest;

class StoreWholesaleFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'budget' => 'required|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|exists:product_categories,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
