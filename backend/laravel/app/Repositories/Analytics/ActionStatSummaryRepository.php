<?php

namespace App\Repositories\Analytics;

use App\Models\ActionStat;
use App\Models\ActionStatSummary;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActionStatSummaryRepository extends BaseRepository
{
    /**
     * Log a raw action stat.
     */
    public function logAction(array $data): ActionStat
    {
        $actionStat = ActionStat::create([
            'actor_id' => $data['actor_id'],
            'actor_type' => $data['actor_type'],
            'action' => $data['action'],
            'subject_id' => $data['subject_id'],
            'subject_type' => $data['subject_type'],
            'metadata' => $data['metadata'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'created_at' => now(),
        ]);

        // Also update the daily summary
        $this->incrementSummary(
            $data['action'],
            $data['subject_id'],
            $data['subject_type']
        );

        return $actionStat;
    }

    /**
     * Increment the daily summary count.
     */
    public function incrementSummary(string $action, int $subjectId, string $subjectType, ?string $date = null): ActionStatSummary
    {
        $date = $date ?? now()->toDateString();

        $summary = ActionStatSummary::firstOrCreate(
            [
                'action' => $action,
                'subject_id' => $subjectId,
                'subject_type' => $subjectType,
                'date' => $date,
            ],
            ['count' => 0]
        );

        $summary->increment('count');

        return $summary;
    }

    /**
     * Get summary stats for a specific date.
     */
    public function getSummaryByDate(
        string $date,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): Collection {
        $query = ActionStatSummary::query()->where('date', $date);

        if ($action) {
            $query->where('action', $action);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }

        return $query->get();
    }

    /**
     * Get summary stats for a date range.
     */
    public function getSummaryByDateRange(
        string $startDate,
        string $endDate,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): Collection {
        $query = ActionStatSummary::query()
            ->whereBetween('date', [$startDate, $endDate]);

        if ($action) {
            $query->where('action', $action);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }

        return $query->orderBy('date')->get();
    }

    /**
     * Get aggregated totals for a date range grouped by action.
     */
    public function getAggregatedByAction(
        string $startDate,
        string $endDate,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): Collection {
        $query = ActionStatSummary::query()
            ->select('action', DB::raw('SUM(count) as total'))
            ->whereBetween('date', [$startDate, $endDate]);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }

        return $query->groupBy('action')->get();
    }

    /**
     * Get daily stats for a subject.
     */
    public function getDailyStatsForSubject(
        int $subjectId,
        string $subjectType,
        string $startDate,
        string $endDate,
        ?string $action = null
    ): Collection {
        $query = ActionStatSummary::query()
            ->where('subject_id', $subjectId)
            ->where('subject_type', $subjectType)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($action) {
            $query->where('action', $action);
        }

        return $query->orderBy('date')->get();
    }

    /**
     * Get monthly aggregated stats.
     */
    public function getMonthlyStats(
        int $year,
        int $month,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): Collection {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = ActionStatSummary::query()
            ->select(
                'action',
                'subject_id',
                'subject_type',
                DB::raw('SUM(count) as total')
            )
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($action) {
            $query->where('action', $action);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }

        return $query->groupBy('action', 'subject_id', 'subject_type')->get();
    }

    /**
     * Get total count for a specific day.
     */
    public function getTotalForDate(
        string $date,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): int {
        $query = ActionStatSummary::query()->where('date', $date);

        if ($action) {
            $query->where('action', $action);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }

        return (int) $query->sum('count');
    }

    /**
     * Get total all-time stats for a subject (grouped by action).
     */
    public function getTotalBySubject(
        int $subjectId,
        string $subjectType
    ): Collection {
        return ActionStatSummary::query()
            ->select('action', DB::raw('SUM(count) as count'))
            ->where('subject_id', $subjectId)
            ->where('subject_type', $subjectType)
            ->groupBy('action')
            ->get();
    }

    /**
     * Get top subjects by action count.
     */
    public function getTopSubjects(
        string $startDate,
        string $endDate,
        string $subjectType,
        ?string $action = null,
        int $limit = 10
    ): Collection {
        $query = ActionStatSummary::query()
            ->select('subject_id', 'subject_type', DB::raw('SUM(count) as total'))
            ->where('subject_type', $subjectType)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($action) {
            $query->where('action', $action);
        }

        return $query
            ->groupBy('subject_id', 'subject_type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }
}
