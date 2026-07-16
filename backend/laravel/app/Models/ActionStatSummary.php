<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActionStatSummary extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'action',
        'subject_id',
        'subject_type',
        'date',
        'count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'count' => 'integer',
    ];

    /**
     * Get the subject (entity being tracked).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Increment the count for a specific action/subject/date combination.
     */
    public static function incrementCount(string $action, int $subjectId, string $subjectType, ?string $date = null): self
    {
        $date = $date ?? now()->toDateString();

        return self::updateOrCreate(
            [
                'action' => $action,
                'subject_id' => $subjectId,
                'subject_type' => $subjectType,
                'date' => $date,
            ],
            []
        )->tap(function ($summary) {
            $summary->increment('count');
        });
    }
}
