<?php

namespace App\Repositories;

use App\Models\NursingHome;

class NursingHomeRepository
{
    public function getNursingHomes(array $filters = [])
    {
        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,main-phone,facebook,website,address',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
                'users.email'
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0);

        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        return $query->get();
    }
}
