<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'user_type', 'phone', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function nursing()
    {
        return $this->hasOne(Nursing::class, 'user_id', 'id');
    }
    
    public function nursingHomes()
    {
        return $this->hasMany(NursingHomeProfile::class, 'user_id', 'id');
    }

    public function member()
    {
        return $this->hasOne(MemberProfile::class, 'user_id', 'id');
    }

    // เช็ค type
    public function isNursing()
    {
        return $this->user_type === 'NURSING';
    }

    public function isNursingHome()
    {
        return $this->user_type === 'NURSING_HOME';
    }

    public function isMember()
    {
        return $this->user_type === 'MEMBER';
    }

    // ดึง profile ตาม user_type
    public function realProfile()
    {
        return match ($this->user_type) {
            'NURSING' => $this->nursing(),
            'NURSING_HOME' => $this->nursingHome(),
            'MEMBER' => $this->member(),
            default => null,
        };
    }

    /**
     * Get all of the rates for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rates()
    {
        return $this->hasMany(Rate::class, 'user_id', 'id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('is_cover', false);
    }

    public function coverImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_cover', true);
    }

}
