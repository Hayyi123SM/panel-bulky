<?php

namespace App\Http\Requests\Api\Order\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class SetAmountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|uuid|exists:invoices,id',
            'amount' => 'required|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
