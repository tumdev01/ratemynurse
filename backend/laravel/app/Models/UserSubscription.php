<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model {
    use SoftDeletes;
    protected $table = 'user_subscriptions';
    protected $fillable = [
        'user_id',
        'plan',
        'subscribable_id',
        'subscribable_type',
        'start_date',
    ];

    public function subscribable()
    {
        return $this->morphTo();
    }
}       