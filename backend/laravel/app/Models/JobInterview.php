<?php

namespace App\Models;

class JobInterview {
    protected $table = 'job_interviews';
    protected $fillable = [
        'job_id',
        'user_id',
        'type',
        'description',
    ];
}