<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetSelectedItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_item_id' => 'required|exists:cart_items,id',
            'is_selected' => 'required|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
