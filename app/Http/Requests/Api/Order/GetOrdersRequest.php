<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

class GetOrdersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|string|in:waiting_payment,orders,split_payment',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|min:3',
            'date' => 'nullable|date|date_format:Y-m-d',
            'status' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
