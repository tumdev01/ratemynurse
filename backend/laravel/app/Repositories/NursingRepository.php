<?php

namespace App\Repositories;

use App\Models\Nursing;
use App\Models\NursingProfile;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;
use App\Enums\UserType;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class NursingRepository
{
    public function createNurse(array $input)
    {
        $user = Nursing::create([
            'user_type'     => UserType::NURSING->value,
            'firstname'     => Arr::get($input, 'firstname'),
            'lastname'      => Arr::get($input, 'lastname'),
            'email'         => Arr::get($input, 'email'),
            'password'      => Hash::make(Arr::get($input, 'phone')),
            'status'        => 1,
            'phone'         => Arr::get($input, 'phone')
        ]);

        if ($user && $user->id) {
            NursingProfile::create([
                'user_id'   => $user->id,
                'name'      => sprintf('%s %s (%s)', $user->firstname, $user->lastname, Arr::get($input, 'nickname')),
                'gender'    => Arr::get($input, 'gender'),
                'date_of_birth' => Arr::get($input, 'date_of_birth'),
                'province_id'   => Arr::get($input, 'province_id'),
                'district_id'   => Arr::get($input, 'district_id'),
                'sub_district_id'  => Arr::get($input, 'sub_district_id'),
                'zipcode'       => Arr::get($input, 'zipcode')
            ]);

            if ($file = Arr::get($input, 'profile_image')) {
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                    $file->move(public_path('images'), $hashedName);

                    Image::create([
                        'user_id'  => $user->id,
                        'type'     => 'NURSING_HOME',
                        'name'     => $file->getClientOriginalName(),
                        'path'     => 'images/' . $hashedName,
                        'filetype' => $file->getClientMimeType(),
                        'is_cover' => true,   // fix: กำหนดตรงนี้เลย
                    ]);
                }
            }
        }

        return $user;
    }

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
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover',
                'rates.rate_details',
                'costs'
            ])
            ->select('id','firstname','lastname','phone','email')
            ->withCount(['rates as review_count'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING')
            ->first();
        return $query;
    }

    public function getInfo(int $id) 
    {
        $nursing = Nursing::query()
            ->with([
                'profile',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover',
                'cv',
                'detail',
                'detail.images:id,detail_id,path'
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING')
            ->first();

        if (!$nursing) {
            return null;
        }

        // รวม rate_details ทั้งหมดสำหรับ global average
        $allDetails = $nursing->rates->flatMap->rate_details;
        $nursing->global_avg = $allDetails->avg('scores');

        // เพิ่ม avg_scores ให้แต่ละ rate
        $nursing->rates->transform(function ($rate) {
            $rateDetails = $rate->rate_details;
            $rate->avg_scores = $rateDetails->avg('scores');
            return $rate;
        });

        return $nursing;
    }

    public function getNursingDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->select(['users.id','users.status'])
            ->whereNull('users.deleted_at')
            ->where('users.user_type', 'NURSING');

        return DataTables::of($query)
            ->addColumn('name', fn($n) => optional($n->profile)->name ?? '-')
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->full_path : '')
            ->addColumn('average_score', function ($n) {
                $allDetails = $n->rates->flatMap->rate_details; // รวม rate_details ทั้งหมด
                return $allDetails->count() > 0 
                    ? number_format($allDetails->avg('scores'), 2) 
                    : '-';
            })
            ->addColumn('review_count', function ($n) {
                return $n->rates->count();
            })
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }
}
