<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberProfile extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'member_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'about',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'sub_district_id',
        'district_id',
        'province_id',
        'zipcode',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_relation',
        'sevices_requuired',
        'privacy',
        'policy',
        'newsletter',
        'cardid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'name' => 'string',
        'about' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'gender' => 'string',
        'date_of_birth' => 'date',
        'address' => 'string',
        'sub_district_id' => 'integer',
        'district_id' => 'integer',
        'province_id' => 'integer',
        'zipcode' => 'string',
        'contact_person_name' => 'string',
        'contact_person_phone' => 'string',
        'contact_person_relation' => 'string',
        'services_required' => 'string',
        'privacy' => 'boolean',
        'policy' => 'boolean',
        'newsletter' => 'boolean',
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

    public function job()
    {
        return $this->belongsTo(Job::class, 'user_id', 'id');
    }


}
