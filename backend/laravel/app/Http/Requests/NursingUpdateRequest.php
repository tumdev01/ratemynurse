<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Nursing;

class NursingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $userId = (int) $this->route('id');
        return [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname'  => ['required', 'string', 'max:50'],
            'nickname'  => ['required', 'string', 'max:25'],
            'user_type' => ['required', 'in:NURSING'],
            'date_of_birth' => ['required', 'date'],
            'blood'     => ['nullable', 'string'],
            'gender'    => ['required', 'string', 'in:MALE,FEMALE,OTHER'],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($userId, 'id'), // 👈 ระบุ column ชัด ๆ
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^\d+$/',
                'size:10',
                Rule::unique('users', 'phone')
                    ->whereNull('deleted_at')
                    ->ignore($userId, 'id'),
            ],

            'address' => ['required', 'string'],
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
        ];
    }
}
