<?php

namespace App\Http\Requests\Api\Order\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|uuid|exists:invoices,id',
            'payment_method' => 'required|uuid|exists:payment_methods,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
