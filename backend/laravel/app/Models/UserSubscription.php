<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UserSubscription extends Model {
    use SoftDeletes;

    protected $table = 'user_subscriptions';

    protected $fillable = [
        'plan',
        'subscribable_id',
        'subscribable_type',
        'start_date',
    ];

    protected $appends = ['end_date'];

    protected $casts = [
        'start_date' => 'date',
    ];

    // ===== Appends =====

    public function getEndDateAttribute(): ?string
    {
        if (!$this->start_date) {
            return null;
        }

        return Carbon::parse($this->start_date)
            ->addMonth()
            ->toDateString(); // 2026-03-09
    }

    // ===== Relations =====

    public function subscribable()
    {
        return $this->morphTo();
    }
}