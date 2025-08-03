<?php

namespace App\Repositories;

use App\Models\Nursing;

class NursingRepository
{
    public function getNursing(array $filters = [])
    {
        return Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost,name',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING')
            ->get();
    }
}
