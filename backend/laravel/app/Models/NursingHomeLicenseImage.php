<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingHomeLicenseImage extends Model {
    protected $table = 'nursing_home_license_images';

    protected $fillable = [
        'profile_id',
        'filename',
        'filetype',
        'path',
        'fullpath'
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url($this->path);
    }

    public function profile() {
        return $this->belongsTo(NursingHomeProfile::class, 'profile_id', 'id');
    }
}