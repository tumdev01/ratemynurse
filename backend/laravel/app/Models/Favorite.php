<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $table = 'favorites';
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'profile_type',
        'profile_id'
    ];

    /**
     * MEMBER ที่กดถูกใจ
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function profile()
    {
        return $this->morphTo(
            name: 'profile',
            type: 'profile_type',
            id: 'profile_id'
        );
    }
}
