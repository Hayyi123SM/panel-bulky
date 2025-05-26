<?php

namespace App\Http\Requests\Api\User\Address;

use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
//            'province_id' => ['required', 'uuid', 'exists:provinces,id'],
//            'city_id' => ['required', 'uuid', 'exists:cities,id'],
//            'district_id' => ['required', 'uuid', 'exists:districts,id'],
//            'sub_district_id' => ['nullable', 'uuid', 'exists:sub_districts,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_primary' => ['required', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
