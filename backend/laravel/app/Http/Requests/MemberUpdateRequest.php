<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->user()->id;
        
        return [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname'  => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', 'in:MALE,FEMALE'],
            'phone' => [
                'required',
                'string',
                'regex:/^0\d{9}$/',
                Rule::unique('users', 'phone')
                    ->ignore($userId)
                    ->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($userId)
                    ->whereNull('deleted_at'),
            ],
            'cardid' => [
                'required',
                'string',
                'regex:/^\d{13}$/',
                Rule::unique('member_profiles', 'cardid')
                    ->ignore($userId, 'user_id')
                    ->whereNull('deleted_at'),
            ],
            'facebook' => ['nullable', 'string', 'max:255'],
            'lineid'   => ['nullable', 'string', 'max:50'],
            'address'  => ['nullable', 'string', 'max:255'],
            'sub_district_id'  => ['nullable', 'integer', 'exists:sub_districts,id'],
            'district_id'  => ['nullable', 'integer', 'exists:districts,id'],
            'province_id'  => ['nullable', 'integer', 'exists:provinces,id'],
            'zipcode'  => ['nullable', 'string', 'regex:/^\d{5}$/'],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',  // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'กรุณาระบุชื่อจริง',
            'lastname.required'  => 'กรุณาระบุนามสกุล',
            'phone.required'     => 'กรุณาระบุหมายเลขโทรศัพท์',
            'phone.regex'        => 'หมายเลขโทรศัพท์ต้องมี 10 ตัว และขึ้นต้นด้วย 0',
            'phone.unique'       => 'หมายเลขโทรศัพท์นี้ถูกใช้งานแล้ว',
            'email.required'     => 'กรุณาระบุอีเมล์',
            'email.email'        => 'รูปแบบอีเมล์ไม่ถูกต้อง',
            'email.unique'       => 'อีเมล์นี้ถูกใช้งานแล้ว',
            'cardid.required'    => 'กรุณาระบุหมายเลขบัตรประชาชน',
            'cardid.regex'       => 'หมายเลขบัตรประชาชนต้องมี 13 ตัวเลข',
            'cardid.unique'      => 'หมายเลขบัตรประชาชนนี้ถูกใช้งานแล้ว',
            'profile_image.image' => 'ไฟล์ต้องเป็นรูปภาพ',
            'profile_image.mimes' => 'ประเภทไฟล์ต้องเป็น jpg, png หรือ webp',
            'profile_image.max'   => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
        ];
    }
}