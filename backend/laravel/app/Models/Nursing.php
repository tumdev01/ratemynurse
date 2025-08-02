<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Nursing extends User
{
    protected $table = 'users'; // <<=== เพิ่มบรรทัดนี้

    protected static function booted()
    {
        static::addGlobalScope('user_type', function (Builder $builder) {
            $builder->where('user_type', 'NURSING');
        });
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }


    public function profile()
    {
        return $this->hasOne(NursingProfile::class, 'user_id', 'id');
    }

    // public function nursing()
    // {
    //     return $this->hasOne(NursingProfile::class);
    // }

    // public function nursingHome()
    // {
    //     return $this->hasOne(NursingHomeProfile::class);
    // }
}
