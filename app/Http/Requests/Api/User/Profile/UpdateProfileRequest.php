<?php

namespace App\Http\Requests\Api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id)
            ],
            'phone_number' => 'required|string|max:20',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
