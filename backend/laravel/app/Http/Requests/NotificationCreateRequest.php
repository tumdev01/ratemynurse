<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|string',
            'is_read' => 'required|boolean',
            'user_id' => 'required|integer',
        ];
    }
}