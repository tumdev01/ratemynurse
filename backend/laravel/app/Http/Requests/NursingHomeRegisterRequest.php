<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingHomeRegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname'  => ['required', 'string', 'max:50'],
            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:10',
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'res_phone' => ['nullable', 'string', 'regex:/^\d+$/', 'size:10'],
            'facebook'  => ['nullable', 'string', 'max:255'],
            'website'   => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:50'],
            'province_id' => ['required', 'integer'],
            'district_id' => ['required', 'integer'],
            'sub_district_id' => ['required', 'integer'],
            'zipcode' => ['required', 'string', 'regex:/^\d{5}$/'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'firstname.required' => 'กรุณาระบุชื่อจริง',
            'firstname.string'   => 'ชื่อต้องเป็นตัวอักษร',
            'firstname.max'      => 'ชื่อจริงความยาวไม่เกิน 50 ตัวอักษร',
            'lastname.required'  => 'กรุณาระบุนามสกุล',
            'lastname.max'       => 'นามสุกลต้องมีความยาวไม่เกิน 50 ตัวอักษร',
            'lastname.string'    => 'นามสกุลต้องเป็นตัวอักษร',
            'phone.required'     => 'กรุณาระบุเบอร์โทรศัพท์',
            'phone.unique'       => 'หมายเลขโทรศัพท์นี้มีผู้ใช้แล้ว',
            'phone.size'         => 'หมายเลขโทรศัพท์ต้องมี 10 ตัว',
            'email.required'     => 'อีเมล์ต้องกรอก',
            'email.email'        => 'รูปแบบอีเมล์ไม่ถูกต้อง',
            'email.unique'       => 'อีเมล์มีผู้ใช้แล้ว',
            'res_phone.size'     => 'หมายเลขโทรศัพท์ต้องมี 10 ตัว',
            'address.required'  => 'กรุณาระบุที่อยู่',
            'address.string'    => 'ที่อยู่ต้องเป็นตัวอักษร',
            'address.max'       => 'ที่อยู่ความยาวไม่เกิน 50 ตัวอักษร',
            'province_id.required'     => 'กรุณาระบุจังหวัด',
            'district_id.required'     => 'กรุณาระบุอำเภอ/เขต',
            'sub_district_id.required' => 'กรุณาระบุตำบล/แขวง',
            'zipcode.required'  => 'กรุณาระบุรหัสไปรษณีย์',
            'zipcode.regex'     => 'รหัสไปรษณีย์ต้องมี 5 หลัก',
        ];
    }
}
