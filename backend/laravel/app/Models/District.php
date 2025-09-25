<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class District
 *
 * @package App\Models
 *
 * @property integer id
 * @property integer province_id
 * @property string name
 * @property-read Province province
 * @property-read Collection|SubDistrict[] subDistricts
 */
class District extends Model
{
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'province_id' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|SubDistrict[]
     */
    public function subDistricts()
    {
        return $this->hasMany(SubDistrict::class);
    }

    /**
     * Get all of the jobs for the District
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'district_id');
    }
}
