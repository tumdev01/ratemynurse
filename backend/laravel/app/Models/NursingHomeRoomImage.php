<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingHomeRoomImage extends Model {
    protected $table = 'nusing_home_room_images';
    protected $fillable = [
        'room_id',
        'path',
        'fullpath',
        'filetype',
        'is_cover'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url($this->path);
    }

    public function room()
    {
        return $this->belongsTo(NursingHomeRoom::class);
    }
}