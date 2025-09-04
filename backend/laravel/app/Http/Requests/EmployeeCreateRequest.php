<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeCreateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'firstname' => [
                'required',
                'string',
                'max:50'
            ],
            'lastname' => [
                'required',
                'string'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone' => [
                'required',
                'string',
                'max:10',
                Rule::unique('users','phone')->whereNull('deleted_at'),
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/[A-Z]/', // ต้องมีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว
                'confirmed'      // ต้องมี confirm_password ที่ตรงกัน
            ],
        ];
    }
    
    public function messages() : array
    {
        return [
            'firstname.required' => 'กรุณาระบุชื่อ',
            'firstname.max' => 'ความยาวชื่อต้องไม่เกิน 50 ตัวอักษร',
            'firstname.string' => 'ต้องเป็นตัวอักษรเท่านั้น',
            'lastname.required' => 'กรุณาระบุนามสกุล',
            'lastname.string' => 'ต้องเป็นตัวอักษรเท่านั้น',
            'email.required' => 'กรุณาระบุที่อยู่อีเมล์',
            'email.email' => 'ต้องเป็นรูปแบบ อีเมล์',
            'email.unique' => 'อีเมล์นี้ถูกใช้ไปแล้ว',
            'password.required' => 'กรุณาระบุรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องมีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            'phone.required' => 'ต้องระบุเบอร์โทรศัพท์',
            'phone.max' => 'ต้องมีแค่ 10 ตัวเท่านั้น',
            'phone.unique' => 'เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว'
        ];
    }
}
