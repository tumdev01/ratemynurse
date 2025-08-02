<?php

namespace App\Repositories;

use App\Models\District;
use App\Models\SubDistrict;
use Illuminate\Database\Eloquent\Builder;

class SubDistrictRepository extends BaseRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|District[]
     */
    public function getSubDistricts()
    {
        return $this->remember('sub_districts', function () {
            return SubDistrict::query()
                ->select(['id', 'name', 'district_id'])
                ->orderBy('district_id')
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @param int $province_id
     * @return \Illuminate\Database\Eloquent\Collection|District[]
     */
    public function getSubDistrictsByProvinceId(int $province_id)
    {
        return $this->cacheForever("sub_districts_{$province_id}", function () use ($province_id) {
            return SubDistrict::query()
                ->select(['id', 'name', 'district_id'])
                ->whereHas('district', function (Builder $query) use ($province_id) {
                    $query->where('province_id', $province_id);
                })
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @param int $district_id
     * @return \Illuminate\Database\Eloquent\Collection|District[]
     */
    public function getSubDistrictsByDistrictId(int $district_id, array $filters = [])
    {
        $term = isset($filters['term']) ? $filters['term'] : '';
        return $this->cacheForever("sub_districts_{$district_id}", function () use ($district_id, $term) {
            return SubDistrict::query()
                ->select(['id', 'name', 'district_id'])
                ->whereHas('district', function (Builder $query) use ($district_id) {
                    $query->where('id', $district_id);
                })
                ->when($term, function($q) use ($term){
                    return $q->where('name', 'like', '%'.$term.'%');
                })
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }
}
