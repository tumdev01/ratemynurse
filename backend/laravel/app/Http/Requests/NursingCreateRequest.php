<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'username')
                    ->where('type', UserType::POLICE->value)
                    ->whereNull('deleted_at'),
            ],
            'password' => [
                'required',
//                'string',
                'confirmed'
            ],
            'name' => ['required', 'string', 'max:50'],
//            'date_of_birth' => ['required', 'date'],
            'tel' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:10',
                Rule::unique('user_profiles', 'tel')->whereNull('deleted_at')
            ],
            'avatar' => [
//                'required',
                'max:20000',
                'mimes:jpeg,png'
            ],
            'address' => ['required', 'string', 'max:255'],
            'station' => ['nullable'],
            'recommender' => ['nullable'],
            'branch_id' => ['nullable'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
//            'id_card.required' => 'กรุณาระบุ เลขประจำตัว',
            'id_card.max' => 'เลขบัตรประจำตัวต้องมีตัวอักษรไม่เกิน 13 ตัวอักษร',
            'id_card.unique' => 'เลขประจำตัวดังกล่าวมีในระบบแล้ว',
            'username.required' => 'กรุณาระบุ ชื่อผู้ใช้',
            'username.unique' => 'ชื่อผู้ใช้ นี้มีอยู่ในระบบแล้ว',
            'username.max' => 'ชื่อผู้ใช้ ต้องมีตัวอักษรไม่เกิน 20 ตัวอักษร',
            'password.required' => 'กรุณาระบุ รหัสผ่าน',
            'password.max' => 'รหัสผ่าน ต้องมีตัวอักษรไม่เกิน 20 ตัวอักษร',
            'password.min' => 'รหัสผ่าน ต้องมีตัวอักษรอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่าน ต้องประกอบด้วยตัวอักษร A-Z หรือ a-z และตัวเลข 0-9',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            'name.required' => 'กรุณาระบุ ชื่อ-สกุล',
            'name.max' => 'ชื่อ-สกุล สามารถมีตัวอักษรได้สูงสุด 50 ตัวอักษร',
//            'date_of_birth.required' => 'กรุณาระบุ วันเกิด',
//            'date_of_birth.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
            'tel.required' => 'กรุณาระบุ เบอร์โทรศัพท์',
            'tel.size' => 'เบอร์โทรศัพท์ ต้องมีตัวอีกษร 10 ตัวอักษร',
            'tel.regex' => 'เบอร์โทรศัพท์ต้องมีแต่ตัวเลขเท่านั้น',
            'tel.unique' => 'เบอร์โทรศัพท์ดังกล่าวมีในระบบแล้ว',
            'avatar.required' => 'กรุณาอัปโหลดรูป',
            'avatar.max' => 'ไฟล์มีขนาดใหญ่เกิน 20MB',
            'avatar.mimes' => 'ไม่สามารถบันทึกได้ เนื่องจากรูปแบบไฟล์ไม่ถูกต้อง',
            'address.required' => 'โปรดระบุที่อยู่',
            'address.max' => 'ที่อยู่ สามารถมีตัวอักษรได้สูงสุด 255 ตัวอักษร',
        ];
    }
}
