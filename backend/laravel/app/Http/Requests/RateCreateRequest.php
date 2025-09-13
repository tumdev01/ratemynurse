<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserType;

class RateCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('user_type', [
                        UserType::NURSING->value,
                        UserType::NURSING_HOME->value,
                    ]);
                }),
            ],
            'author_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ((int) $value === 0) {
                        return true; // allow admin case
                    }

                    $exists = \App\Models\User::where('id', $value)
                        ->where('user_type', UserType::MEMBER->value)
                        ->exists();

                    if (! $exists) {
                        $fail('author_id ต้องเป็นสมาชิกประเภท MEMBER หรือ 0 (admin)');
                    }
                },
            ],
            'scores' => ['required', 'integer', 'min:1', 'max:5'],
            'scores_for' => ['required'],
            'text' => ['required'],
            'name' => ['required'],
            'description' => ['required'],
            'user_type' => [
                'required',
                Rule::in([
                    UserType::NURSING->value,
                    UserType::NURSING_HOME->value,
                ]),
            ],
        ];
    }

    public function messages() : array
    {
        return [
            '*.required' => 'ไม่สามารถเว้นว่างได้',
            'scores.min' => 'ค่าต่ำที่สุดต้องเป็น 1',
            'scores.max' => 'ค่าสูงสุดไม่เกิน 5',
            '*.integer' => 'ต้องเป็นจำนวนเต็ม',
            'user_id.exists' => 'user_id ต้องเป็นประเภท NURSING หรือ NURSING_HOME เท่านั้น',
            'author_id.exists' => 'author_id ต้องเป็นประเภท MEMBER เท่านั้น',
        ];
    }
}
