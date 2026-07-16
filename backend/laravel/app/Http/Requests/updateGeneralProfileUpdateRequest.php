<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserType;
class updateGeneralProfileUpdateRequest extends FormRequest {
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('user_type', UserType::NURSING_HOME->value),
            ],
            'name' => [
                'required',
                'string',
                'min:10',
                'max:100'
            ],
            'main_phone' => [
                'string',
                'regex:/^\d{10}$/',
            ],
            'res_phone' => [
                'nullable',
                'string',
                'regex:/^\d{10}$/',
            ],
            'facebook' => ['nullable', 'url'],
            'website'  => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:255'],
            'map_show' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID ไม่ถูกต้อง',
        ];
    }
}