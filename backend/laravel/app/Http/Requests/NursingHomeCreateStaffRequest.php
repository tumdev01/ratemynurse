<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingHomeCreateStaffRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required'],
            'name' => ['required', 'string'],
            'responsibility' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ผู้ให้บริการไม่ถูกต้อง',
            'name.required' => 'ต้องระบุชื่อ-สกุล',
            'name.string'   => 'ชื่อ-สกุล ต้องเป็นตัวอักษร',
            'responsibility.required' => 'ต้องระบุหน้าที่รับผิด่ชอบ',
        ];
    }
}