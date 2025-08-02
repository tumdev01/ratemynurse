<?php

namespace App\Repositories;

use App\Models\District;

class DistrictRepository extends BaseRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|District[]
     */
    public function getDistricts()
    {
        return $this->remember('districts', function () {
            return District::query()
                ->select(['id', 'name', 'province_id'])
                ->orderBy('province_id')
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @param int $province_id
     * @return \Illuminate\Database\Eloquent\Collection|District[]
     */
    public function getDistrictsByProvinceId(int $province_id, array $filters = [])
    {
        $term = isset($filters['term']) ? $filters['term'] : '';
        return $this->cacheForever("districts_{$province_id}", function () use ($province_id, $term) {
            return District::query()
                ->select(['id', 'name'])
                ->where('province_id', $province_id)
                ->when($term, function($q) use ($term){
                    return $q->where('name', 'like', '%'.$term.'%');
                })
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }
}
