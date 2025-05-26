<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetSelectedItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_items' => 'required|array|min:1',
            'cart_items.*.id' => 'required|uuid|exists:cart_items,id',
            'cart_items.*.selected' => 'required|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
