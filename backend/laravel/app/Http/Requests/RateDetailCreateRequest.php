<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RateDetailCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'rate_id' => [
                'required',
                Rule::exists('rates', 'id')->where(function ($query) {
                    $query->Arr::whereNotNull('deleted_at');
                }),
            ],
            'scores' => ['min:1', 'max:5', 'required', 'integer'],
            'scores_for' => ['required'],
        ];
    }

    public function messages() : array
    {
        return [
            '*.required' => 'ไม่สามารถเว้นว่างได้',
            '*.integer' => 'ต้องเป็นจำนวนเต็ม',
        ];
    }
}