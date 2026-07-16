<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddFavoriteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'profile_type' => [
                'required',
                'string',
                Rule::in(['NURSING', 'NURSING_HOME']),
            ],

            'profile_id' => [
                'required',
                'integer',

                Rule::when(
                    $this->profile_type === 'NURSING',
                    Rule::exists('nursing_profiles', 'id')
                ),

                Rule::when(
                    $this->profile_type === 'NURSING_HOME',
                    Rule::exists('nursing_home_profiles', 'id')
                ),

                // ✅ ต้องอยู่ตรงนี้
                Rule::unique('favorites')
                    ->where('user_id', $this->user_id)
                    ->where('profile_type', $this->profile_type),
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'กรุณาระบุผู้ใช้งาน',
            'user_id.exists'   => 'ไม่พบผู้ใช้งานในระบบ',

            'profile_type.required' => 'กรุณาระบุประเภทโปรไฟล์',
            'profile_type.in'       => 'ประเภทโปรไฟล์ไม่ถูกต้อง',

            'profile_id.required' => 'กรุณาระบุ profile_id',
            'profile_id.integer'  => 'profile_id ต้องเป็นตัวเลข',

            'profile_id.exists' => $this->profile_type === 'NURSING'
                ? 'ไม่พบ Nursing Profile ที่ระบุ'
                : 'ไม่พบ Nursing Home Profile ที่ระบุ',

            'profile_id.unique' => 'คุณได้เพิ่มรายการนี้เป็นรายการโปรดแล้ว',
        ];
    }
}