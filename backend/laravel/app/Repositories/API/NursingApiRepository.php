<?php

namespace App\Repositories\API;

use App\Models\Nursing;
use App\Models\NursingProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;
use Illuminate\Support\Carbon;

class NursingApiRepository
{
    public function createNurse(array $input)
    {
        return DB::transaction(function () use ($input) {
            $nursing = Nursing::create([
                'firstname'  => $input['firstname'],
                'lastname'   => $input['lastname'],
                'email'      => $input['email'],
                'password'   => Hash::make($input['phone']),
                'phone'      => $input['phone'],
                'status'     => 1,
                'plan'       => 'BASIC',
                'plan_start' => Carbon::today()->toDateString(),
                'user_type'  => 'NURSING',
            ]);

            // If create() failed, throw exception
            if (!$nursing || !$nursing->exists) {
                throw new \Exception('Failed to create Nursing user.');
            }

            $profile = NursingProfile::create([
                'user_id' => $nursing->id,
                'name'    => $nursing->firstname . ' ' . $nursing->lastname,
                'nickname'=> Arr::get($input, 'nickname') ?? '',
                'gender'  => Arr::get($input, 'gender') ?? '',
                'blood'   => Arr::get($input, 'blood') ?? '',
                'date_of_birth' => Arr::get($input, 'date_of_birth') ?? '',
                'medical_condition' => Arr::get($input, 'medical_condition') ?? '',
                'history_of_drug_allergy' => Arr::get($input, 'history_of_drug_allergy') ?? '',
            ]);

            if (!$profile || !$profile->exists) {
                throw new \Exception('Failed to create Nursing user.');
            }

            return $nursing;
        });
    }
}
