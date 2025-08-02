<?php

namespace App\Repositories;

use App\Models\Nursing;

class NursingRepository
{
    public function getNursing(array $filters = [])
    {
        return Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
                'users.firstname',
                'users.lastname',
                'users.email'
            ])
            ->get();
    }
}
