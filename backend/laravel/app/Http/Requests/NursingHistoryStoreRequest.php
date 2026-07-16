<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NursingHistoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = (int) $this->route('id');
        return [
            'graducated' => 'required|string',
            'edu_ins' => 'nullable|string',
            'graducated_year' => 'nullable|digits:4',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'cert_date' => 'nullable|date',
            'cert_expire' => 'nullable|date|after:cert_date',
            'cert_no' => 'nullable|string',
            'cert_etc' => 'nullable|string',
            'extra_courses' => 'nullable|string',
            'current_workplace' => 'nullable|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'exp' => 'nullable|numeric|min:0|max:20',
            'work_type' => 'nullable|string',
            'extra_shirft' => 'nullable|string',
            'languages' => 'nullable|string',
            'cvs_images' => 'nullable|array',
            'cvs_images.*' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,pdf,webp',
                'max:2048'
            ],
        ];
    }

    public function messages()
    {
        return [
            'graducated.required' => 'วุฒิการศึกษาไม่ถูกต้อง',
            'graducated.string' => 'วุฒิการศึกษาไม่ถูกต้อง',
            'edu_ins.string' => 'สถาบันการศึกษาไม่ถูกต้อง',
            'graducated_year.digits' => 'ปีการศึกษาไม่ถูกต้อง',
            'gpa.numeric' => 'GPAไม่ถูกต้อง',
            'gpa.min' => 'GPAไม่ถูกต้อง',
            'gpa.max' => 'GPAไม่ถูกต้อง',
            'cert_date.date' => 'วันที่ไม่ถูกต้อง',
            'cert_expire.date' => 'วันที่ไม่ถูกต้อง',
            'cert_expire.after' => 'วันที่ไม่ถูกต้อง',
            'cert_no.string' => 'เลขที่ไม่ถูกต้อง',
            'cert_etc.string' => 'อื่นๆไม่ถูกต้อง',
            'extra_courses.string' => 'หลักสูตรเพิ่มเติมไม่ถูกต้อง',
            'current_workplace.string' => 'สถานที่ปัจจุบันไม่ถูกต้อง',
            'department.string' => 'แผนกไม่ถูกต้อง',
            'position.string' => 'ตำแหน่งไม่ถูกต้อง',
            'exp.numeric' => 'ประสบการณ์ไม่ถูกต้อง',
            'exp.min' => 'ประสบการณ์ไม่ถูกต้อง',
            'exp.max' => 'ประสบการณ์ไม่ถูกต้อง',
            'work_type.string' => 'ประเภทงานไม่ถูกต้อง',
            'extra_shirft.string' => 'ชั่วโมงพิเศษไม่ถูกต้อง',
            'languages.string' => 'ภาษาไม่ถูกต้อง',
            'cvs_images.*.mimes' => 'ไฟล์ต้องเป็น jpeg, png, jpg, gif, pdf หรือ webp เท่านั้น',
            'cvs_images.*.max' => 'ไฟล์ต้องมีขนาดไม่เกิน 2MB',
            'cvs_images.*.file' => 'กรุณาอัปโหลดไฟล์ที่ถูกต้อง',
        ];
    }
}
