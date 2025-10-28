<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class JobInterviewApplyRequest extends FormRequest  {
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
            'job_id' => [
                'required',
                Rule::exists('jobs', 'id')
            ],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('user_type', [
                        UserType::NURSING->value,
                        UserType::NURSING_HOME->value
                    ]);
                }),
            ],
            'description' => ['string', 'required'],
        ];
    }
}