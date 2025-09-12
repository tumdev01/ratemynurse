<?php

namespace App\Repositories;

use App\Models\Nursing;
use Illuminate\Support\Arr;

class NursingRepository
{
    public function getNursing(array $filters = [])
    {
        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0);
        
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

    public function getNursingPagination(array $filters = [])
    {
        $order = Arr::get($filters, 'order', 'DESC');
        $orderby = Arr::get($filters, 'orderby', 'created_at');
        $limit = Arr::get($filters, 'limit', 10); // ตั้งค่า default limit
        $certified = Arr::get($filters, 'certified', 0);
        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select(['users.id'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->orderBy($orderby, $order);

        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }
        return $query->paginate($limit);
    }

    public function getNursingById(Int $id)
    {
        $query = Nursing::query()
            ->with([
                'profile',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates:user_id,scores,text,name,description',
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover'
            ])
            ->withAvg('rates as average_score', 'scores')
            ->withCount('rates as review_count')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING')
            ->first();
        return $query;
    }
}
