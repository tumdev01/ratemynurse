<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveFavoriteRequest extends FormRequest
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

                Rule::exists('favorites', 'profile_id')
                    ->where('user_id', $this->user_id)
                    ->where('profile_type', $this->profile_type),
            ],
        ];
    }

    public function messages()
    {
        return [
            'profile_id.exists' => 'ไม่พบรายการโปรดที่ต้องการลบ',
        ];
    }
}
