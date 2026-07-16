<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscriptionRequest extends Model
{
    use SoftDeletes;

    protected $table = 'user_subscription_requests';

    protected $fillable = [
        'user_id',
        'profile_id',
        'type',
        'plan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(UserSubscriptionLog::class, 'subscription_request_id');
    }
}
