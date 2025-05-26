<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetPaymentMethodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_type' => 'required|string|in:single_payment,split_payment',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
