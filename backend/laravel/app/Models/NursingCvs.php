<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingCvs extends Model {
    protected $table = 'nursing_cvs';

    protected $fillable = [
        'user_id',
        'graducated',
        'edu_ins',
        'graducated_year',
        'gpa',
        'cert_no',
        'cert_date',
        'cert_expire',
        'cert_etc',
        'extra_courses',
        'current_workplace',
        'department',
        'position',
        'exp',
        'work_type',
        'extra_shirft',
        'languages',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(NursingCvImage::class, 'cv_id', 'id');
    }
}