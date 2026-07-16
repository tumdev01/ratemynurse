<?php
namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RateProviderRequest extends FormRequest {

    public function authorize(): bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('user_type', [
                        UserType::NURSING->value,
                        UserType::NURSING_HOME->value,
                    ]);
                }),
            ],
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['nullable', 'string'],
            'user_type' => [
                'required',
                Rule::in([
                    UserType::NURSING->value,
                    UserType::NURSING_HOME->value,
                ]),
            ],
            'rateable_id' => ['required', 'integer'],
            'rateable_type' => [
                'required',
                Rule::in([
                    'App\\Models\\NursingProfile',
                    'App\\Models\\NursingHomeProfile',
                ]),
            ],
            'images'    => ['nullable', 'array', 'max:5'],
            'images.*'  => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $author = $this->user();
            if (!$author) {
                $validator->errors()->add('auth', 'กรุณาเข้าสู่ระบบ');
                return;
            }

            // ตรวจสอบว่า author เป็น MEMBER
            if ($author->user_type !== UserType::MEMBER->value) {
                $validator->errors()->add('auth', 'เฉพาะสมาชิกประเภท MEMBER เท่านั้นที่สามารถรีวิวได้');
            }

            // ตรวจสอบรีวิวซ้ำ
            $userId = $this->input('user_id');
            $exists = \App\Models\Rate::where('user_id', $userId)
                ->where('author_id', $author->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('user_id', 'คุณได้ให้คะแนนผู้ให้บริการคนนี้แล้ว');
            }
        });
    }

    public function messages() : array
    {
        return [
            'user_id.required' => 'User ID ไม่ถูกต้อง',
            'user_id.exists' => 'user_id ต้องเป็นประเภท NURSING หรือ NURSING_HOME เท่านั้น',
            '*.required' => 'ไม่สามารถเว้นว่างได้',
            'scores.required' => 'กรุณาเลือกความประทับใจอย่างน้อย 1 รายการ',
            'scores.*.min' => 'ค่าต่ำที่สุดต้องเป็น 1',
            'scores.*.max' => 'ค่าสูงสุดไม่เกิน 5',
            '*.integer' => 'ต้องเป็นจำนวนเต็ม',
            'rateable_id.required' => 'rateable_id จำเป็นต้องระบุ',
            'rateable_type.required' => 'rateable_type จำเป็นต้องระบุ',
            'images.max' => 'อัปโหลดรูปภาพได้สูงสุด 5 รูป',
            'images.*.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
            'images.*.mimes' => 'รองรับเฉพาะไฟล์ jpg, jpeg, png, webp',
            'images.*.max' => 'ขนาดรูปภาพต้องไม่เกิน 5MB',
        ];
    }
}