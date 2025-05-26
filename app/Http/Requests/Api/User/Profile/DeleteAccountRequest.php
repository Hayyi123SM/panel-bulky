<?php

namespace App\Http\Requests\Api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'current_password'
            ]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
