<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActionStat extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'subject_id',
        'subject_type',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the actor (user performing the action).
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject (entity being acted upon).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
