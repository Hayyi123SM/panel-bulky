<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'address_id' => 'required|uuid|exists:addresses,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
