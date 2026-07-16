<?php

namespace App\Http\Requests;

use App\Enums\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(ActionType::values())],
            'subject_id' => ['required', 'integer'],
            'subject_type' => ['required', 'string', Rule::in([
                'App\\Models\\NursingProfile',
                'App\\Models\\NursingHomeProfile',
            ])],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action is required',
            'action.in' => 'Invalid action type',
            'subject_id.required' => 'Subject ID is required',
            'subject_type.required' => 'Subject type is required',
            'subject_type.in' => 'Invalid subject type',
        ];
    }
}
