<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SearchFiendRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'required|string'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
