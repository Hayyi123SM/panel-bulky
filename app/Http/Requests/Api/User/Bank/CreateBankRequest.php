<?php

namespace App\Http\Requests\Api\User\Bank;

use Illuminate\Foundation\Http\FormRequest;

class CreateBankRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_id' => ['required', 'exists:banks,id'],
            'account_name' => ['required', 'string'],
            'account_number' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
