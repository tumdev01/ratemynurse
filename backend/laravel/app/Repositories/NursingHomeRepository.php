<?php

namespace App\Repositories;

use App\Models\NursingHome;
use Illuminate\Support\Arr;

class NursingHomeRepository
{
    public function getNursingHomes(array $filters = [])
    {
        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING_HOME');

        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }


        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }

        return $query->get();
    }

    public function getNuringHomePagination(array $filters = [])
    {
        $order = Arr::get($filters, 'order', 'DESC');
        $orderby = Arr::get($filters, 'orderby', 'created_at');
        $limit = Arr::get($filters, 'limit', 8); // ตั้งค่า default limit
        $certified = Arr::get($filters, 'certified', 0);

        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING_HOME');

        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }
        return $query->paginate($limit);
    }

    public function getInfo(int $id) 
    {
        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id' , $id)
            ->where('user_type', 'NURSING_HOME');
        return $query->first();
    }
}
