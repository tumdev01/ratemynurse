<?php

namespace App\Repositories;

use App\Models\NursingHome;
use App\Models\Province;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;

class NursingHomeRepository
{
    public function getNursingHomes(array $filters = [])
    {
        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates:user_id,scores,text,name,description',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select([
                'users.id',
            ])
            ->withAvg('rates as average_score', 'scores')
            ->withCount('rates as review_count')
            ->whereNull('users.deleted_at')
            ->where('users.status', '!=', 0)
            ->where('users.user_type', 'NURSING_HOME');

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
        $orderby = Arr::get($filters, 'orderby', 'created_at') ?: 'created_at';
        $order   = Arr::get($filters, 'order', 'DESC');
        $limit = Arr::get($filters, 'limit', 8); // ตั้งค่า default limit
        $certified = Arr::get($filters, 'certified');

        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates:user_id,scores,text,name,description',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select([
                'users.id',
            ])
            ->withAvg('rates as average_score', 'scores')
            ->withCount('rates as review_count')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('user_type', 'NURSING_HOME');

        if (array_key_exists('certified', $filters)) {
            $certified = $filters['certified'];

            if (is_null($certified) || $certified === 'null') {
                // ถ้าเป็น null → เอาทั้ง 0 และ 1
                $query->whereHas('profile', function ($q) {
                    $q->whereIn('certified', [0, 1]);
                });
            } else {
                $certified = filter_var($certified, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                $query->whereHas('profile', function ($q) use ($certified) {
                    $q->where('certified', $certified ? 1 : 0);
                });
            }
        }



        if(isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->whereHas('profile', function ($q) use ($province_id){
                $q->where('province_id', $province_id);
            });
        }
        return $query->orderBy($orderby, $order)->paginate($limit);
    }

    public function getInfo(int $id) 
    {
        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates:user_id,scores,text,name,description',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->withAvg('rates as average_score', 'scores')
            ->withCount('rates as review_count')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING_HOME')
            ->first();

        return $query;
    }

    public function getNursingHomeDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'created_at') ?: 'created_at';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = NursingHome::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,cost_per_day,cost_per_month,home_service_type,special_facilities,facilities,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates:user_id,scores,text,name,description',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select(['users.id'])
            ->withAvg('rates as average_score', 'scores')
            ->withCount('rates as review_count')
            ->whereNull('users.deleted_at')
            ->where('users.status', '!=', 0)
            ->where('users.user_type', 'NURSING_HOME');

        // Filter certified
        if (array_key_exists('certified', $filters)) {
            $certified = $filters['certified'];

            if (is_null($certified) || $certified === 'null') {
                $query->whereHas('profile', fn($q) => $q->whereIn('certified', [0, 1]));
            } else {
                $certified = filter_var($certified, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $query->whereHas('profile', fn($q) => $q->where('certified', $certified ? 1 : 0));
            }
        }

        // Filter province
        if(isset($filters['province'])) {
            $province_id = Province::where('code', $filters['province'])->value('id');
            $query->whereHas('profile', fn($q) => $q->where('province_id', $province_id));
        }

        // ใช้ DataTables
        return DataTables::of($query)
            ->addColumn('name', fn($n) => optional($n->profile)->name ?? '-') // ตรวจสอบ profile.name
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->full_path : '')
            ->addColumn('average_score', fn($n) => number_format($n->average_score, 2))
            ->addColumn('review_count', fn($n) => $n->review_count)
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

}
