<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingCvImage extends Model {
    protected $table = 'cv_images';

    protected $fillable = [
        'user_id',
        'cv_id',
        'path',
        'filetype'
    ];

    public function cv()
    {
        return $this->belongsTo(NursingCv::class);
    }
}