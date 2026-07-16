<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserType;

class updateAboutProfileRequest extends FormRequest {
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('user_type', UserType::NURSING_HOME->value),
            ],

            'license_no' => ['required', 'string', 'max:255'],
            'license_start_date' => ['required', 'date'],
            'license_exp_date' => ['required', 'date', 'after:license_start_date'],
            'license_by' => ['nullable', 'string', 'max:255'],
            'certificates' => ['nullable', 'string'],
            'hospital_no' => ['nullable', 'string', 'max:255'],

            'cost_per_day' => ['nullable', 'integer', 'min:0'],
            'cost_per_month' => ['nullable', 'integer', 'min:0'],
            'deposit' => ['nullable', 'integer', 'min:0'],
            'registration_fee' => ['nullable', 'integer', 'min:0'],
            'special_food_expenses' => ['nullable', 'integer', 'min:0'],
            'physical_therapy_fee' => ['nullable', 'integer', 'min:0'],
            'delivery_fee' => ['nullable', 'integer', 'min:0'],
            'laundry_service' => ['nullable', 'integer', 'min:0'],

            'social_security' => ['nullable', 'boolean'],
            'private_health_insurance' => ['nullable', 'boolean'],
            'installment' => ['nullable', 'boolean'],

            'payment_methods' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'กรุณาระบุผู้ใช้งาน',
            'user_id.exists' => 'ไม่พบข้อมูลสถานดูแลผู้สูงอายุนี้ในระบบ',

            'license_no.required' => 'กรุณากรอกเลขที่ใบอนุญาต',
            'license_no.string' => 'เลขที่ใบอนุญาตต้องเป็นข้อความ',
            'license_no.max' => 'เลขที่ใบอนุญาตต้องไม่เกิน 255 ตัวอักษร',

            'license_start_date.required' => 'กรุณาระบุวันที่เริ่มต้นใบอนุญาต',
            'license_start_date.date' => 'รูปแบบวันที่เริ่มต้นใบอนุญาตไม่ถูกต้อง',

            'license_exp_date.required' => 'กรุณาระบุวันที่หมดอายุใบอนุญาต',
            'license_exp_date.date' => 'รูปแบบวันที่หมดอายุใบอนุญาตไม่ถูกต้อง',
            'license_exp_date.after' => 'วันที่หมดอายุต้องมากกว่าวันที่เริ่มต้นใบอนุญาต',

            'license_by.string' => 'หน่วยงานที่ออกใบอนุญาตต้องเป็นข้อความ',
            'license_by.max' => 'หน่วยงานที่ออกใบอนุญาตต้องไม่เกิน 255 ตัวอักษร',

            'certificates.string' => 'ข้อมูลใบรับรองต้องเป็นข้อความ',

            'hospital_no.string' => 'เลขที่สถานพยาบาลต้องเป็นข้อความ',
            'hospital_no.max' => 'เลขที่สถานพยาบาลต้องไม่เกิน 255 ตัวอักษร',

            'cost_per_day.integer' => 'ค่าบริการต่อวันต้องเป็นตัวเลข',
            'cost_per_day.min' => 'ค่าบริการต่อวันต้องไม่น้อยกว่า 0',

            'cost_per_month.integer' => 'ค่าบริการต่อเดือนต้องเป็นตัวเลข',
            'cost_per_month.min' => 'ค่าบริการต่อเดือนต้องไม่น้อยกว่า 0',

            'deposit.integer' => 'ค่ามัดจำต้องเป็นตัวเลข',
            'deposit.min' => 'ค่ามัดจำต้องไม่น้อยกว่า 0',

            'registration_fee.integer' => 'ค่าลงทะเบียนต้องเป็นตัวเลข',
            'registration_fee.min' => 'ค่าลงทะเบียนต้องไม่น้อยกว่า 0',

            'special_food_expenses.integer' => 'ค่าอาหารพิเศษต้องเป็นตัวเลข',
            'special_food_expenses.min' => 'ค่าอาหารพิเศษต้องไม่น้อยกว่า 0',

            'physical_therapy_fee.integer' => 'ค่ากายภาพบำบัดต้องเป็นตัวเลข',
            'physical_therapy_fee.min' => 'ค่ากายภาพบำบัดต้องไม่น้อยกว่า 0',

            'delivery_fee.integer' => 'ค่ารับ-ส่งต้องเป็นตัวเลข',
            'delivery_fee.min' => 'ค่ารับ-ส่งต้องไม่น้อยกว่า 0',

            'laundry_service.integer' => 'ค่าบริการซักรีดต้องเป็นตัวเลข',
            'laundry_service.min' => 'ค่าบริการซักรีดต้องไม่น้อยกว่า 0',

            'social_security.boolean' => 'ข้อมูลประกันสังคมไม่ถูกต้อง',
            'private_health_insurance.boolean' => 'ข้อมูลประกันสุขภาพเอกชนไม่ถูกต้อง',
            'installment.boolean' => 'ข้อมูลการผ่อนชำระไม่ถูกต้อง',

            'payment_methods.string' => 'วิธีการชำระเงินต้องเป็นข้อความ',
        ];
    }
}