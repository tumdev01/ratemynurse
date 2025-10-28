<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NursingHomeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'main_phone' => [
                'string',
                'regex:/^\d{10}$/',
                // Rule::unique('nursing_home_profiles', 'main_phone')
                // ->ignore($this->nursingHomeProfileId(), 'id')
                // ->whereNull('deleted_at'),
            ],
            'res_phone' => [
                'nullable',
                'string',
                'regex:/^\d{10}$/',
                // Rule::unique('nursing_home_profiles', 'res_phone')
                // ->ignore($this->nursingHomeProfileId(), 'id')
                // ->whereNull('deleted_at'),
            ],

            'facebook' => ['nullable', 'url'],
            'website'  => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:50'],
            'license_start_date' => ['nullable', 'date'],
            'license_exp_date' => ['nullable', 'date'],
            'license_by' => ['nullable', 'string', 'max:100'],

            'certificates' => ['nullable', 'string'],

            'hospital_no' => ['nullable', 'string', 'max:100'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'graduated' => ['nullable', 'string', 'max:100'],
            'graduated_paper' => ['nullable', 'string'],
            'exp_year' => ['nullable', 'integer', 'min:0', 'max:100'],

            'manager_phone' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'manager_email' => ['nullable', 'email'],

            'assist_name' => ['nullable', 'string', 'max:100'],
            'assist_no' => ['nullable', 'string', 'max:100'],
            'assist_expert' => ['nullable', 'string', 'max:255'],
            'assist_phone' => ['nullable', 'string', 'regex:/^\d{10}$/'],

            'building_no' => ['nullable', 'integer', 'min:0'],
            'total_room' => ['nullable', 'integer', 'min:0'],
            'private_room_no' => ['nullable', 'integer', 'min:0'],
            'duo_room_no' => ['nullable', 'integer', 'min:0'],
            'shared_room_three_beds' => ['nullable', 'integer', 'min:0'],
            'max_serve_no' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],

            // Facilities (boolean)
            'nurse_station' => ['nullable', 'boolean'],
            'emergency_room' => ['nullable', 'boolean'],
            'examination_room' => ['nullable', 'boolean'],
            'medicine_room' => ['nullable', 'boolean'],
            'kitchen_cafeteria' => ['nullable', 'boolean'],
            'dining_room' => ['nullable', 'boolean'],
            'activity_room' => ['nullable', 'boolean'],
            'physical_therapy_room' => ['nullable', 'boolean'],
            'meeting_room' => ['nullable', 'boolean'],
            'office_room' => ['nullable', 'boolean'],
            'laundry_room' => ['nullable', 'boolean'],
            'elevator' => ['nullable', 'boolean'],
            'wheelchair_ramp' => ['nullable', 'boolean'],
            'bathroom_grab_bar' => ['nullable', 'boolean'],
            'emergency_bell' => ['nullable', 'boolean'],
            'camera' => ['nullable', 'boolean'],
            'fire_extinguishing_system' => ['nullable', 'boolean'],
            'backup_generator' => ['nullable', 'boolean'],
            'air_conditioner' => ['nullable', 'boolean'],
            'garden_area' => ['nullable', 'boolean'],
            'parking' => ['nullable', 'boolean'],
            'wifi_internet' => ['nullable', 'boolean'],
            'central_television' => ['nullable', 'boolean'],
            'ambulance' => ['nullable', 'boolean'],
            'ambulance_amount' => ['nullable', 'integer', 'min:0'],
            'van_shuttle' => ['nullable', 'boolean'],
            'special_medical_equipment' => ['nullable', 'string', 'max:255'],

            // Staff count
            'total_staff' => ['nullable', 'integer', 'min:0'],
            'total_fulltime_nurse' => ['nullable', 'integer', 'min:0'],
            'total_parttime_nurse' => ['nullable', 'integer', 'min:0'],
            'total_nursing_assistant' => ['nullable', 'integer', 'min:0'],
            'total_regular_doctor' => ['nullable', 'integer', 'min:0'],
            'total_physical_therapist' => ['nullable', 'integer', 'min:0'],
            'total_pharmacist' => ['nullable', 'integer', 'min:0'],
            'total_nutritionist' => ['nullable', 'integer', 'min:0'],
            'total_social_worker' => ['nullable', 'integer', 'min:0'],
            'total_general_employees' => ['nullable', 'integer', 'min:0'],
            'total_security_officer' => ['nullable', 'integer', 'min:0'],

            // Costs
            'cost_per_day' => ['nullable', 'numeric', 'min:0'],
            'cost_per_month' => ['nullable', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'special_food_expenses' => ['nullable', 'numeric', 'min:0'],
            'physical_therapy_fee' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],

            // Support
            'laundry_service' => ['nullable', 'numeric'],
            'social_security' => ['nullable', 'boolean'],
            'private_health_insurance' => ['nullable', 'boolean'],
            'installment' => ['nullable', 'boolean'],
            'payment_methods' => ['nullable', 'string'],
            'payment_methods.*' => ['string'],

            // Description
            'center_highlights' => ['nullable', 'string'],
            'patients_target' => ['nullable', 'string'],
            'visiting_time' => ['nullable', 'string'],
            'patient_admission_policy' => ['nullable', 'string'],
            'emergency_contact_information' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],

            // Location
            'province_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'sub_district_id' => ['nullable', 'integer'],
            'zipcode' => ['nullable', 'string', 'regex:/^\d{5}$/'],
            'map' => ['nullable', 'string'],
            'youtube_url' => ['nullable', 'string'],
            'map_embed' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // User ID
            'user_id.required' => 'กรุณาระบุรหัสผู้ใช้',
            'user_id.integer' => 'รหัสผู้ใช้ต้องเป็นตัวเลข',
            'user_id.unique' => 'รหัสผู้ใช้นี้ถูกใช้ไปแล้ว',

            // Basic Info
            'name.required' => 'กรุณาระบุชื่อสถานบริการ',
            'name.string' => 'ชื่อสถานบริการต้องเป็นข้อความ',
            'name.max' => 'ชื่อสถานบริการต้องไม่เกิน 50 ตัวอักษร',

            'description.required' => 'กรุณาระบุคำอธิบาย',
            'description.string' => 'คำอธิบายต้องเป็นข้อความ',
            'description.max' => 'คำอธิบายต้องไม่เกิน 255 ตัวอักษร',

            // Phone
            'main-phone.required' => 'กรุณาระบุเบอร์โทรหลัก',
            'main-phone.string' => 'เบอร์โทรหลักต้องเป็นข้อความ',
            'main-phone.regex' => 'เบอร์โทรหลักต้องมี 10 หลัก และเป็นตัวเลขเท่านั้น',
            'main-phone.unique' => 'เบอร์โทรหลักนี้ถูกใช้ไปแล้ว',

            'res-phone.string' => 'เบอร์โทรสำรองต้องเป็นข้อความ',
            'res-phone.regex' => 'เบอร์โทรสำรองต้องมี 10 หลัก และเป็นตัวเลขเท่านั้น',
            'res-phone.unique' => 'เบอร์โทรสำรองนี้ถูกใช้ไปแล้ว',

            // URLs
            'facebook.url' => 'ลิงก์ Facebook ต้องอยู่ในรูปแบบ URL ที่ถูกต้อง',
            'website.url' => 'ลิงก์เว็บไซต์ต้องอยู่ในรูปแบบ URL ที่ถูกต้อง',

            // Location
            'province_id.integer' => 'จังหวัดต้องเป็นตัวเลข',
            'district_id.integer' => 'อำเภอต้องเป็นตัวเลข',
            'sub_district_id.integer' => 'ตำบลต้องเป็นตัวเลข',
            'zipcode.regex' => 'รหัสไปรษณีย์ต้องเป็นตัวเลข 5 หลัก',

            // License
            'license_no.max' => 'เลขใบอนุญาตต้องไม่เกิน 50 ตัวอักษร',
            'license_start_date.date' => 'วันที่เริ่มต้นใบอนุญาตไม่ถูกต้อง',
            'license_exp_date.date' => 'วันหมดอายุใบอนุญาตไม่ถูกต้อง',
            'license_by.max' => 'หน่วยงานที่ออกใบอนุญาตต้องไม่เกิน 100 ตัวอักษร',

            'graduated_paper.file' => 'ไฟล์ใบจบการศึกษาต้องเป็นไฟล์',
            'graduated_paper.mimes' => 'ใบจบการศึกษาต้องเป็น pdf, jpg, jpeg หรือ png',

            // Manager & Assist
            'manager_phone.regex' => 'เบอร์โทรผู้จัดการต้องมี 10 หลัก และเป็นตัวเลขเท่านั้น',
            'manager_email.email' => 'อีเมลผู้จัดการต้องเป็นอีเมลที่ถูกต้อง',
            'assist_phone.regex' => 'เบอร์โทรผู้ช่วยต้องมี 10 หลัก และเป็นตัวเลขเท่านั้น',

            // Counts
            '*.integer' => ':attribute ต้องเป็นตัวเลขจำนวนเต็ม',
            '*.numeric' => ':attribute ต้องเป็นตัวเลข',
            '*.min' => ':attribute ต้องมีค่ามากกว่าหรือเท่ากับ 0',

            // Facilities & Booleans
            '*.boolean' => ':attribute ต้องเป็นค่าจริงหรือเท็จ',

            // Payment Methods
            'payment_methods.array' => 'วิธีชำระเงินต้องเป็นรายการ',
            'payment_methods.*.string' => 'แต่ละวิธีชำระเงินต้องเป็นข้อความ',

            // Others
            '*.string' => ':attribute ต้องเป็นข้อความ',
            '*.max' => ':attribute ต้องไม่เกิน :max ตัวอักษร',
        ];
    }

    protected function nursingHomeProfileId()
    {
        // สมมติ route parameter คือ nursing_home id
        $nursingHomeId = $this->route('id');

        $profile = \App\Models\NursingHomeProfile::where('user_id', $nursingHomeId)->first();

        return $profile ? $profile->id : null;
    }

}
