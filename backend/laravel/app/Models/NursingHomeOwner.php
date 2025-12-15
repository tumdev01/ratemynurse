<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;

class NursingHomeOwner extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('user_type', function (Builder $builder) {
            $builder->where('user_type', 'NURSING_HOME_OWNER');
        });
    }

    public function nursingHomes()
    {
        return $this->hasMany(NursingHome::class, 'user_id', 'id');
    }
}
