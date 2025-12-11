<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingCvImage extends Model {
    protected $table = 'cv_images';

    protected $fillable = [
        'user_id',
        'cv_id',
        'name',
        'path',
        'filetype'
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url($this->path);
    }

    public function cv()
    {
        return $this->belongsTo(NursingCv::class);
    }
}