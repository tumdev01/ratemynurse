<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NursingRepository extends BaseRepository
{
    public function getNursing(array $filters)
    {
        return User::query()
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
            ->where('user_type', UserType::NURSING)
            ->get();
    }
}
