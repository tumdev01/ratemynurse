<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NursingProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nursing_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'job_role',
        'religion',
        'about',
        'description',
        'gender',
        'date_of_birth',
        'nationality',
        'cost',
        'sub_district_id',
        'district_id',
        'province_id',
        'zipcode',
        'certified',
        'exp_year',
        'work_style',
        'skill',
        'service_packages',
        'nickname',
        'blood',
        'address',
        'medical_condition',
        'history_of_drug_allergy'
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
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function subDistrict()
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id', 'id');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'profile');
    }

    public function isFavoritedBy($user)
    {
        if (!$user) return false;

        return $this->favorites()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function memberContacts()
    {
        return $this->morphMany(MemberContact::class, 'provider');
    }
}
