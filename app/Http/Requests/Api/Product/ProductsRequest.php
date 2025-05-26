<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|min:3|max:255',
            'warehouse' => 'nullable|uuid',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
            'condition' => 'nullable|string',
            'brands' => 'nullable|array|min:1',
            'brands.*' => 'string|distinct',
            'per_page' => 'nullable|integer|min:1',
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:10000',
            'random' => 'nullable|boolean'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
