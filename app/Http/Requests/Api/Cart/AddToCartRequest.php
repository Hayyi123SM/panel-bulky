<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|uuid|exists:products,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
