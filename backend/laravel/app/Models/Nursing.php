<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasSubscriptions;

class Nursing extends User
{
    protected $table = 'users';

    use HasApiTokens; // << important
    use HasSubscriptions;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'phone',
        'status',
        'plan',
        'plan_start',
        'user_type'
    ];
    protected static function booted()
    {
        static::addGlobalScope('user_type', function (Builder $builder) {
            $builder->where('user_type', 'NURSING');
        });
    }

    public function profile()
    {
        return $this->hasOne(NursingProfile::class, 'user_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'user_id', 'id')->where('is_cover', false);
    }

    public function coverImage()
    {
        return $this->hasOne(Image::class, 'user_id', 'id')->where('is_cover', true);
    }
    
    public function rates()
    {
        return $this->hasMany(Rate::class, 'user_id', 'id');
    }

    public function costs()
    {
        return $this->hasMany(NursingCost::class, 'user_id', 'id');
    }

}
