<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model 
{
    use SoftDeletes;

    protected $table = 'jobs';

    protected $fillable = [
        'user_id',
        'user_id',
        'name',
        'service_type',
        'hire_type',
        'hire_rule',
        'care_type',
        'cost',
        'start_date',
        'description',
        'address',
        'province_id',
        'district_id',
        'sub_district_id',
        'phone',
        'email',
        'facebook',
        'lineid',
        'status'
    ];

    /**
     * Get the user that owns the Job
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province that owns the Job
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Get the district that owns the Job
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Get the user that owns the Job
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sub_district(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id');
    }

    /**
     * Get the user that owns the Job
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(MemberProfile::class, 'user_id', 'user_id');
    }
}