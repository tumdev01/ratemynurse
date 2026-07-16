<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscriptionLog extends Model
{
    protected $table = 'user_subscription_logs';

    protected $fillable = [
        'subscription_request_id',
        'user_id',
        'action',
        'performed_by',
        'note',
    ];

    public function request()
    {
        return $this->belongsTo(UserSubscriptionRequest::class, 'subscription_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
