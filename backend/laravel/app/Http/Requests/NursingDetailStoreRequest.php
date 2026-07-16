<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NursingDetailStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = (int) $this->route('id');
        return [
            'about' => 'nullable|string',
            'skills' => 'nullable',
            'other_skills' => 'nullable|string',
            'detail_images' => 'nullable|array',
            'detail_images.*' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:2048'
            ],
        ];
    }

    public function messages()
    {
        return [
            'about.string' => 'เกี่ยวกับไม่ถูกต้อง',
            'skills.string' => 'ทักษะไม่ถูกต้อง',
            'other_skills.string' => 'ทักษะอื่นๆไม่ถูกต้อง',
            'detail_images.*.mimes' => 'ไฟล์ต้องเป็น jpeg, png, jpg, gif, pdf หรือ webp เท่านั้น',
            'detail_images.*.max' => 'ไฟล์ต้องมีขนาดไม่เกิน 2MB',
            'detail_images.*.file' => 'กรุณาอัปโหลดไฟล์ที่ถูกต้อง',
        ];
    }
}