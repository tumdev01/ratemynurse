<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'religion',
        'about',
        'gender',
        'date_of_birth',
        'nationality',
        'province_id',
        'district_id',
        'sub_district_id',
        'zipcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'date_of_birth' => 'date',
        'province_id' => 'integer',
        'district_id' => 'integer',
        'sub_district_id' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function subDistrict()
    {
        return $this->hasOne(SubDistrict::class, 'id', 'sub_district_id');
    }

}
