<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|uuid|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
            'images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
