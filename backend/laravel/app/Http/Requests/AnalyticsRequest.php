<?php

namespace App\Http\Requests;

use App\Enums\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'action' => ['nullable', 'string', Rule::in(ActionType::values())],
            'subject_id' => ['nullable', 'integer'],
            'subject_type' => ['nullable', 'string', Rule::in([
                'App\\Models\\NursingProfile',
                'App\\Models\\NursingHomeProfile',
            ])],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date_format' => 'Start date must be in Y-m-d format',
            'end_date.date_format' => 'End date must be in Y-m-d format',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'action.in' => 'Invalid action type',
            'subject_type.in' => 'Invalid subject type',
        ];
    }
}
