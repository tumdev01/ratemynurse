<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use App\Enums\UserType;

class JobCreateRequest extends FormRequest 
{
    protected function prepareForValidation()
    {
        if ($this->user()) {
            // เพิ่ม user_id ลงใน request
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }
    }

    public function rules()
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('user_type', [
                        UserType::MEMBER->value
                    ]);
                }),
            ],
            'name' => ['string', 'required'],
            'service_type' => ['required', 'string', 'in:NURSING,NURSING_HOME'],
            'hire_type' => ['required', 'string', 'in:DAILY,WEEKLY,MONTHLY,YEARLY'],
            'cost' => ['required', 'numeric', 'min:100'],
            'start_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')],
            'district_id' => [
                'required', 'integer',
                Rule::exists('districts', 'id')->where(function ($query) {
                    $query->where('province_id', Request::input('province_id'));
                }),
            ],
            'sub_district_id' => [
                'required', 'integer',
                Rule::exists('sub_districts', 'id')->where(function ($query) {
                    $query->where('district_id', Request::input('district_id'));
                }),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:10',
            ],
            'email' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'lineid' => [
                'nullable',
                'string',
                'regex:/^@.+$/', // ต้องขึ้นต้นด้วย @ ตามด้วยตัวอักษรใดๆ
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'กรุณาเลือกผู้ใช้',
            'user_id.exists' => 'ผู้ใช้ที่เลือกไม่ถูกต้องหรือไม่ใช่สมาชิก',

            'name.required' => 'กรุณากรอกชื่อ',
            'name.string' => 'ชื่อต้องเป็นตัวอักษร',

            'service_type.required' => 'กรุณาเลือกประเภทบริการ',
            'service_type.string' => 'ประเภทบริการต้องเป็นตัวอักษร',
            'service_type.in' => 'ประเภทบริการต้องเป็น NURSING หรือ NURSING_HOME',

            'hire_type.required' => 'กรุณาเลือกประเภทการจ้าง',
            'hire_type.string' => 'ประเภทการจ้างต้องเป็นตัวอักษร',
            'hire_type.in' => 'ประเภทการจ้างต้องเป็น DAILY, WEEKLY, MONTHLY หรือ YEARLY',

            'cost.required' => 'กรุณากรอกค่าใช้จ่าย',
            'cost.numeric' => 'ค่าใช้จ่ายต้องเป็นตัวเลข',
            'cost.min' => 'ค่าใช้จ่ายต้องไม่น้อยกว่า 100 บาท',

            'start_date.required' => 'กรุณาเลือกวันที่เริ่มงาน',
            'start_date.date' => 'วันที่เริ่มงานไม่ถูกต้อง',

            'description.required' => 'กรุณากรอกคำอธิบาย',
            'description.string' => 'คำอธิบายต้องเป็นข้อความ',

            'address.required' => 'กรุณากรอกที่อยู่',
            'address.string' => 'ที่อยู่ต้องเป็นข้อความ',

            'province_id.required' => 'กรุณาเลือกจังหวัด',
            'province_id.integer' => 'จังหวัดไม่ถูกต้อง',
            'province_id.exists' => 'จังหวัดที่เลือกไม่ถูกต้อง',

            'district_id.required' => 'กรุณาเลือกอำเภอ',
            'district_id.integer' => 'อำเภอไม่ถูกต้อง',
            'district_id.exists' => 'อำเภอไม่อยู่ในจังหวัดที่เลือก',

            'sub_district_id.required' => 'กรุณาเลือกตำบล',
            'sub_district_id.integer' => 'ตำบลไม่ถูกต้อง',
            'sub_district_id.exists' => 'ตำบลไม่อยู่ในอำเภอที่เลือก',

            'phone.required' => 'กรุณากรอกหมายเลขโทรศัพท์',
            'phone.string' => 'หมายเลขโทรศัพท์ต้องเป็นข้อความ',
            'phone.regex' => 'หมายเลขโทรศัพท์ต้องเป็นตัวเลขเท่านั้น',
            'phone.size' => 'หมายเลขโทรศัพท์ต้องมี 10 หลัก',

            'email.string' => 'อีเมลต้องเป็นข้อความ',
            'facebook.string' => 'Facebook ต้องเป็นข้อความ',
            'lineid.string' => 'Line ID ต้องเป็นข้อความ',
            'lineid.regex' => 'Line ID ต้องขึ้นต้นด้วย @',
        ];
    }
}