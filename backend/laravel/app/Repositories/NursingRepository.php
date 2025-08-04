<?php

namespace App\Repositories;

use App\Models\Nursing;

class NursingRepository
{
    public function getNursing(array $filters = [])
    {
        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING');
        
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        if (!empty($filters['certified'])) {
            $query->whereHas('profile', function ($q) {
                $q->where('certified', 1);
            });
        }

        return $query->get();
    }
}
