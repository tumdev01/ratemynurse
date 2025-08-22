<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;

class NursingHome extends User
{
    protected $table = 'users'; // <<=== เพิ่มบรรทัดนี้

    protected static function booted()
    {
        static::addGlobalScope('user_type', function (Builder $builder) {
            $builder->where('user_type', 'NURSING_HOME');
        });
    }

    // Accessor สำหรับ name ของ Nursing Home
    public function getNameAttribute()
    {
        return $this->profile?->name; // null-safe operator
    }

    public function profile()
    {
        return $this->hasOne(NursingHomeProfile::class, 'user_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'user_id', 'id')->where('is_cover', false);
    }

    public function coverImage()
    {
        return $this->hasOne(Image::class, 'user_id', 'id')->where('is_cover', true);
    }
}
