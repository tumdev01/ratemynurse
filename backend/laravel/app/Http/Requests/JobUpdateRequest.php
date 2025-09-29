<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use App\Enums\UserType;

class JobUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['string', 'required'],
            'service_type' => ['required', 'string', 'in:NURSING,NURSING_HOME'],
            'care_type' => ['required', 'string', 'in:RN,PN,NA,CG,MAID,ETC'],
            'hire_type' => ['required', 'string', 'in:DAILY,WEEKLY,MONTHLY,YEARLY'],
            'hire_rule' => ['required', 'string', 'in:FULL_STAY,FULL_ROUND,PART_STAY,PART_ROUND'],
            'cost' => ['required', 'numeric', 'min:100'],
            'start_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')],
            'district_id' => [
                'required', 'integer',
                Rule::exists('districts', 'id')->where(fn ($q) => 
                    $q->where('province_id', Request::input('province_id'))
                ),
            ],
            'sub_district_id' => [
                'required', 'integer',
                Rule::exists('sub_districts', 'id')->where(fn ($q) => 
                    $q->where('district_id', Request::input('district_id'))
                ),
            ],
            'phone' => ['required','string','regex:/^\d+$/','size:10'],
            'email' => ['nullable','string'],
            'facebook' => ['nullable','string'],
            'lineid' => ['nullable','string','regex:/^@.+$/'],
        ];
    }
}
