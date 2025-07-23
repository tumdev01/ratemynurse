<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Nursing extends User
{
    /**
     * Booted method to add a global scope
     * ให้ดึงเฉพาะ user_type = 'NURSING'
     */
    protected static function booted()
    {
        static::addGlobalScope('user_type', function (Builder $builder) {
            $builder->where('user_type', 'NURSING');
        });
    }
}
