<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class FavoriteSetRequest extends FormRequest
{
    public function rules()
    {
        return [
            'profile_id' => 'required|integer',
            'profile_type' => 'required|in:App\Models\NursingProfile,App\Models\NursingHomeProfile',
        ];
    }
}
