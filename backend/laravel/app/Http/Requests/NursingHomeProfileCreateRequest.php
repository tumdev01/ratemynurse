<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingHomeProfileCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'address' => ['required', 'string', 'max:50'],
            'province_id' => ['required', 'integer'],
            'district_id' => ['required', 'integer'],
            'sub_district_id' => ['required', 'integer'],
            'zipcode' => ['required', 'string', 'regex:/^\d{5}$/'],
            'user_id' => ['required'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'address.required' => 'กรุณาระบุที่อยู่',
            'address.string'   => 'ที่อยู่ต้องเป็นตัวอักษร',
            'address.max'      => 'ที่อยู่ความยาวไม่เกิน 50 ตัวอักษร',
            'user_id.required' => 'ข้อมูลผู้ใช้ไม่ถูกต้อง'
        ];
    }
}
