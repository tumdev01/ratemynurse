<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberCreateRequest extends FormRequest
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
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'cardid' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:13',
                Rule::unique('member_profiles', 'cardid')->whereNull('deleted_at'),
            ],
            'user_type' => ['required', 'string', 'in:MEMBER'],
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
            'phone.unique'       => 'หมายเลขโทรศัพท์นี้มีผู้ใช้แล้ว',
            'phone.size'         => 'หมายเลขโทรศัพท์ต้องมี 10 ตัว',
            'email.unique'       => 'อีเมล์มีผู้ใช้แล้ว',
            'email.required'     => 'อีเมล์ต้องกรอก',
            'cardid.size'        => 'หมายเลขประจำตัวประชาชน ต้องมี 13 หลัก',
            'cardid.unique'      => 'หมายเลขประจำตัวประชาชน มีผู้ใช้แล้ว',
            'cardid.required'    => 'หมายเลขประจำตัวประชาชน ไม่ถูกต้อง'
        ];
    }
}
