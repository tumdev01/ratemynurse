<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\NursingHomeRoomImage;

class NursingHomeRoom extends Model {

    protected $table = 'nursing_home_rooms';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cost_per_day',
        'cost_per_month',
        'type',
        'active'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(NursingHomeRoomImage::class, 'room_id', 'id');
    }

    public function coverImage()
    {
        return $this->hasOne(NursingHomeRoomImage::class, 'room_id', 'id')->where('is_cover', true);
    }
}