<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberContactCreateRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules()
    {
        return [
            'provider_id'  => ['required'],
            'provider_role'=> ['required'],
            'provider_type'=> ['nullable'],
            'description'     => ['required', 'string', 'min:10'],
            'phone'      => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],

            'email'      => ['nullable', 'email'],
            'lineid'       => ['nullable', 'string'],
            'facebook'   => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'กรุณากรอกรายละเอียดงาน',
            'description.min'      => 'รายละเอียดงานต้องมีอย่างน้อย :min ตัวอักษร',

            'phone.required'  => 'กรุณากรอกเบอร์โทรศัพท์',

            'start_date.required' => 'กรุณาเลือกวันที่เริ่มต้น',
            'start_date.date'     => 'รูปแบบวันที่เริ่มต้นไม่ถูกต้อง',

            'end_date.required' => 'กรุณาเลือกวันที่สิ้นสุด',
            'end_date.date'     => 'รูปแบบวันที่สิ้นสุดไม่ถูกต้อง',
            'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',

            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        ];
    }
}