<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model {
    protected $table = 'user_subscription';
    protected $fillable = [
        'user_id',
        'plan',
        'start_date'
    ];
}