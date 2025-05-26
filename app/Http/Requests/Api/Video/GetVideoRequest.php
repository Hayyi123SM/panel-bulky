<?php

namespace App\Http\Requests\Api\Video;

use Illuminate\Foundation\Http\FormRequest;

class GetVideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'paginate' => 'required|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
            'take' => 'nullable|integer|min:1|max:100'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
