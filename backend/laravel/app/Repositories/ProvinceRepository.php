<?php

namespace App\Repositories;

use App\Models\Province;

class ProvinceRepository extends BaseRepository
{
    /**
     * @return Province[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getProvinceDropdown(array $filters = [])
    {
        $term = isset($filters['term']) ? $filters['term'] : '';
        return $this->cacheForever('provinces', function () use ($term) {
            return Province::query()
                ->select(['id', 'name'])
                ->when($term, function($q) use ($term){
                    return $q->where('name', 'like', '%'.$term.'%');
                })
                ->orderBy('id')
                ->orderBy('name')
                ->get();
        });
    }
}
