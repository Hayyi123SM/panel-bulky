<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetShippingMethodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'method' => 'required|string|in:self_pickup,courier_pickup',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
