<?php

namespace App\Http\Requests\Api\User\Bank;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'exists:bank_accounts,id'],
            'bank_id' => ['required', 'exists:banks,id'],
            'account_name' => ['required', 'string'],
            'account_number' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        $user = $this->user();
        $bankAccount = $user->banks()->where('id', $this->input('id'))->first();

        if (!$bankAccount) {
            return false;
        }
        return true;
    }
}
