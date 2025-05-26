<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_type' => 'required|string|in:single_payment,split_payment',
            'notes' => 'nullable|string|max:255',
            'friend_ids.*' => 'required_if:payment_type,split_payment|uuid|exists:users,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
