<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SetSelectedItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // select_all_type only allowed 'palet', required if cart_item_id/is_selected not present
            'select_all_type' => 'required_without_all:cart_item_id,is_selected|in:palet',
            'cart_item_id' => 'required_with:is_selected|exists:cart_items,id',
            'is_selected' => 'required_with:cart_item_id|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
