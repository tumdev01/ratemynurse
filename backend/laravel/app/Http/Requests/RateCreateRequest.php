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
            'author_id' => ['required'],
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
        ];
    }
}