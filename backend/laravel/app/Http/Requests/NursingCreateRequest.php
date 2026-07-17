<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingCreateRequest extends FormRequest
{
    /**
     * ตัดอักขระที่ไม่ใช่ตัวเลขออกจากเบอร์โทรศัพท์ก่อน validate (เช่น "02-xxx-xxxx", "+66-xx-xxx-xxxx")
     * แล้วแปลงรหัสประเทศ 66 นำหน้า (ถ้ามี แทนเลข 0) กลับให้เป็น 0 นำหน้าตามปกติ
     */
    protected function prepareForValidation()
    {
        if ($this->filled('phone')) {
            $digits = preg_replace('/\D+/', '', $this->input('phone'));

            if (str_starts_with($digits, '66') && strlen($digits) === 11) {
                $digits = '0' . substr($digits, 2);
            }

            $this->merge(['phone' => $digits]);
        }
    }

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
            'nickname'  => ['required', 'string', 'max:25'],
            'user_type' => ['required', 'in:NURSING'],
            'date_of_birth' => ['nullable', 'date'],
            'blood'     => ['nullable', 'string'],
            'gender'    => ['required', 'string', 'in:MALE,FEMALE,OTHER'],
            'care_type' => ['nullable', 'string', 'in:RN,PN,NA,CG,MAID'],
            'certified' => ['nullable', 'boolean'],
            'email' => [
                'required',
                'string',
                Rule::unique('users', 'email')->whereNull('deleted_at')->whereNotNull('phone_verified_at'),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:10',
                // เบอร์ที่สมัครไปแล้วแต่ยังไม่เคยยืนยัน OTP สำเร็จ ไม่นับว่าซ้ำ — ให้สมัครซ้ำได้เพื่อ resend OTP
                Rule::unique('users', 'phone')->whereNull('deleted_at')->whereNotNull('phone_verified_at'),
            ],
            // ฟอร์ม Laravel admin (nursing.store) ส่งไฟล์รูปมาเป็น "profile_image" และไม่บังคับใส่รูป —
            // ส่วน WP plugin/API ส่งมาเป็น "profile_photo" และยังคงบังคับใส่รูปเหมือนเดิม
            ...($this->routeIs('nursing.store')
                ? ['profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120']]
                : ['profile_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120']]),
            'medical_condition_detail' => ['nullable', 'string'],
            'history_of_drug_allergy_detail' => ['nullable', 'string'],
            // 'address' => ['required', 'string', 'max:255'],
            // 'province_id' => ['required', 'integer'],
            // 'district_id' => ['required', 'integer'],
            // 'sub_district_id' => ['required', 'integer'],
            // 'zipcode' => ['required', 'string', 'regex:/^\d{5}$/'],
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
            'nickname.required'  => 'ต้องระบุชื่อเล่น',
            'nickname.max'       => 'ชื่อเล่นต้องมีความยาวไม่เกิน 25 ตัวอักษร',
            'nickname.string'    => 'ชื่อเล่นต้องเป็นตัวอักษร',
            'phone.unique'       => 'หมายเลขโทรศัพท์นี้มีผู้ใช้แล้ว',
            'phone.size'         => 'หมายเลขโทรศัพท์ต้องมี 10 ตัว',
            'email.unique'       => 'อีเมล์นี้มีผู้ใช้งานแล้ว',
            'date_of_birth.date' => 'วัน/เดือน/ปีเกิด ไม่ถูกต้อง',
            'profile_photo.required' => 'กรุณาอัปโหลดรูปถ่าย',
            'profile_photo.image'    => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'profile_photo.mimes'    => 'รองรับเฉพาะไฟล์ .jpg, .jpeg, .png, .webp',
            'profile_photo.max'      => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
            'profile_image.image'   => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'profile_image.mimes'   => 'รองรับเฉพาะไฟล์ .jpg, .jpeg, .png, .webp',
            'profile_image.max'     => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
        ];
    }
}
