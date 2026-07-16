<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\FacilityType;
use App\Enums\SpecialFacilityType;
use App\Enums\HomeServiceType;
use App\Enums\AdditionalServiceType;
use App\Enums\CenterHighlightType;

class updateMoreInfoProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'about' => ['required', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string'],
            'building_no' => ['required', 'integer', 'min:0'], 
            'total_room' => ['required', 'integer', 'min:0'], 
            'private_room_no' => ['required', 'integer', 'min:0'], 
            'duo_room_no' => ['required', 'integer', 'min:0'], 
            'shared_room_three_beds' => ['required', 'integer', 'min:0'], 
            'max_serve_no' => ['required', 'integer', 'min:0'], 
            'area' => ['required', 'numeric', 'min:0'],
            'ambulance' => ['required', 'boolean'], 
            'ambulance_amount' => ['required', 'integer', 'min:0'], 
            'van_shuttle' => ['required', 'boolean'], 
            'special_medical_equipment' => ['nullable', 'string'],
            'home_service_type' => ['required', 'array'],
            'home_service_type.*' => [
                'required',
                'string',
                Rule::in(HomeServiceType::keys()),
            ],
            'etc_services' => ['nullable', 'string'],
            'additional_service_type' => ['required', 'array'],
            'additional_service_type.*' => [
                'required',
                'string',
                Rule::in(AdditionalServiceType::keys()),
            ],

            'center_highlights' => ['required', 'array'],
            'center_highlights.*' => [
                'required',
                'string',
                Rule::in(CenterHighlightType::keys()),
            ],

            'facilities' => ['required', 'array'],
            'facilities.*' => [
                'required',
                Rule::in(FacilityType::keys()),
            ],

            'special_facilities' => ['required', 'array'],
            'special_facilities.*' => [
                'required',
                Rule::in(SpecialFacilityType::keys()),
            ],
        ];
    }

    public function messages(): array
    {
        return [

            // area
            'area.required' => 'กรุณากรอกขนาดพื้นที่',
            'area.numeric'  => 'ขนาดพื้นที่ต้องเป็นตัวเลขเท่านั้น',
            'area.min'      => 'ขนาดพื้นที่ต้องมากกว่าหรือเท่ากับ 0',

            // home_service_type
            'home_service_type.required' => 'กรุณาเลือกประเภทบริการดูแลที่บ้าน',
            'home_service_type.array'    => 'รูปแบบข้อมูลประเภทบริการไม่ถูกต้อง',
            'home_service_type.*.required' => 'พบค่าประเภทบริการดูแลที่บ้านว่างเปล่า',
            'home_service_type.*.in' => 'มีค่าประเภทบริการดูแลที่บ้านไม่ถูกต้อง',

            // additional_service_type
            'additional_service_type.required' => 'กรุณาเลือกบริการเพิ่มเติม',
            'additional_service_type.array'    => 'รูปแบบข้อมูลบริการเพิ่มเติมไม่ถูกต้อง',
            'additional_service_type.*.required' => 'พบค่าบริการเพิ่มเติมว่างเปล่า',
            'additional_service_type.*.in' => 'มีค่าบริการเพิ่มเติมไม่ถูกต้อง',

            // center_highlights
            'center_highlights.required' => 'กรุณาเลือกจุดเด่นของศูนย์',
            'center_highlights.array'    => 'รูปแบบข้อมูลจุดเด่นของศูนย์ไม่ถูกต้อง',
            'center_highlights.*.required' => 'พบค่าจุดเด่นว่างเปล่า',
            'center_highlights.*.in' => 'มีค่าจุดเด่นของศูนย์ไม่ถูกต้อง',

            // facilities
            'facilities.required' => 'กรุณาเลือกสิ่งอำนวยความสะดวก',
            'facilities.array'    => 'รูปแบบข้อมูลสิ่งอำนวยความสะดวกไม่ถูกต้อง',
            'facilities.*.required' => 'พบค่าสิ่งอำนวยความสะดวกว่างเปล่า',
            'facilities.*.in' => 'มีค่าสิ่งอำนวยความสะดวกไม่ถูกต้อง',

            // special_facilities
            'special_facilities.required' => 'กรุณาเลือกสิ่งอำนวยความสะดวกพิเศษ',
            'special_facilities.array'    => 'รูปแบบข้อมูลสิ่งอำนวยความสะดวกพิเศษไม่ถูกต้อง',
            'special_facilities.*.required' => 'พบค่าสิ่งอำนวยความสะดวกพิเศษว่างเปล่า',
            'special_facilities.*.in' => 'มีค่าสิ่งอำนวยความสะดวกพิเศษไม่ถูกต้อง',
        ];
    }
}