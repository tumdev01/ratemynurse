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

    public function profile()
    {
        return $this->hasOne(NursingProfile::class, 'user_id', 'id');
    }

}
