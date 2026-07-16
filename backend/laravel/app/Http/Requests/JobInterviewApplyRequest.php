<?php
namespace App\Http\Requests;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class JobInterviewApplyRequest extends FormRequest  {
    protected function prepareForValidation()
    {
        if ($this->user()) {
            $user = $this->user();
            $profile = match ($user->user_type) {
                'NURSING'      => NursingProfile::where('user_id', $user->id)->first(),
                'NURSING_HOME' => NursingHomeProfile::where('user_id', $user->id)->first(),
                default        => null,
            };
            $this->merge([
                'profile_id' => $profile?->id,
                'type'       => $user->user_type,
            ]);
        }
    }

    public function messages()
    {
        return [
            'job_id.unique' => 'คุณได้สมัครงานนี้ไปแล้ว',
        ];
    }

    public function rules()
    {
        return [
            'job_id'         => [
                'required',
                Rule::exists('jobs', 'id'),
                Rule::unique('job_interviews')
                    ->where('profile_id', $this->profile_id)
                    ->where('type', $this->type),
            ],
            'profile_id'     => ['required', 'integer'],
            'message'        => ['required', 'string', 'min:100'],
            'price'          => ['required', 'numeric', 'min:1'],
            'start_date'     => ['required', 'date'],
            'attach_profile' => ['nullable', 'boolean'],
        ];
    }
}