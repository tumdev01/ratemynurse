<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use \App\Traits\HasSubscriptions;
use App\Traits\Favoritable;

class NursingProfile extends Model
{
    use HasFactory, SoftDeletes, HasSubscriptions, Favoritable;

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
        'care_type',
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
        'history_of_drug_allergy',
        'medical_condition_detail',
        'history_of_drug_allergy_detail'
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

    protected $appends = ['summary_cost', 'summary_cost_by_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('is_cover', false);
    }

    public function coverImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_cover', true);
    }

    public function favoritedUsers()
    {
        return $this->favorites()
            ->with('user')   // MEMBER
            ->latest();
    }

    public function memberContacts()
    {
        return $this->morphMany(MemberContact::class, 'provider');
    }

    public function rates()
    {
        return $this->morphMany(Rate::class, 'rateable');
    }

    public function costs()
    {
        return $this->hasMany(NursingCost::class, 'user_id', 'user_id');
    }

    public function getSummaryCostAttribute()
    {
        // ถ้ามี eager loaded costs → ใช้ collection
        if ($this->relationLoaded('costs')) {
            return [
                'lower_cost'  => $this->costs->min('cost'),
                'higher_cost' => $this->costs->max('cost'),
            ];
        }

        // fallback → query ครั้งเดียว
        $summary = $this->costs()
            ->selectRaw('MIN(cost) as lower_cost, MAX(cost) as higher_cost')
            ->first();

        return [
            'lower_cost'  => $summary->lower_cost ?? 0,
            'higher_cost' => $summary->higher_cost ?? 0,
        ];
    }

    public function getSummaryCostByTypeAttribute()
    {
        $costs = $this->relationLoaded('costs')
            ? $this->costs
            : $this->costs()->get();

        return $costs
            ->groupBy('type')
            ->map(fn ($items) => [
                'lower_cost'  => $items->min('cost'),
                'higher_cost' => $items->max('cost'),
            ]);
    }

}
