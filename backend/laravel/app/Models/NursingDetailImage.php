<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingDetailImage extends Model {
    protected $table = 'nursing_detail_images';

    protected $fillable = [
        'user_id',
        'detail_id',
        'filename',
        'path',
        'filetype'
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url($this->path);
    }

    public function detail()
    {
        return $this->belongsTo(NursingDetail::class, 'detail_id', 'id');
    }
}