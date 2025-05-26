<?php

namespace App\Http\Requests\Api\Auth\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
//            'province_id' => 'required|uuid|exists:provinces,id',
//            'city_id' => 'required|uuid|exists:cities,id',
//            'district_id' => 'required|uuid|exists:districts,id',
//            'sub_district_id' => 'required|uuid|exists:sub_districts,id',
//            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', Password::defaults()],
            'device_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
