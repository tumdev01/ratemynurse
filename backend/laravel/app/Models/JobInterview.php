<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobInterview extends Model {
    protected $table = 'job_interviews';
    protected $fillable = [
        'job_id',
        'user_id',
        'class_type',
        'profile_id',
        'type',
        'description',
        'price',
        'start_date',
        'attach_profile',
    ];

    protected $casts = [
        'start_date' => 'date',
        'attach_profile' => 'boolean',
    ];

    protected $appends = ['profile_name'];

    protected $hidden = ['nursingProfile', 'nursingHomeProfile'];

    public function getProfileNameAttribute()
    {
        if ($this->class_type === NursingHome::class) {
            return $this->nursingHomeProfile?->name;
        }

        return $this->nursingProfile?->name;
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        if ($this->class_type === NursingHome::class) {
            return $this->belongsTo(NursingHomeProfile::class, 'profile_id');
        }

        return $this->belongsTo(NursingProfile::class, 'profile_id');
    }

    public function nursingProfile()
    {
        return $this->belongsTo(NursingProfile::class, 'profile_id');
    }

    public function nursingHomeProfile()
    {
        return $this->belongsTo(NursingHomeProfile::class, 'profile_id');
    }
}