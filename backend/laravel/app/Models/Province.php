<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    public $timestamps = false;

    protected $table = "provinces";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|District[]
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get all of the jobs for the Province
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'province_id');
    }

}
