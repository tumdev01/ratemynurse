<?php

namespace App\Services\Analytics;

use App\Enums\ActionType;
use App\Models\ActionStat;
use App\Repositories\Analytics\ActionStatSummaryRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ActionAnalyticsService
{
    protected string $tz = 'Asia/Bangkok';
    protected array $actions = [
        'profile_view',
        'click_call',
        'click_contact',
    ];
    public function __construct(
        protected ActionStatSummaryRepository $repository
    ) {}

    /**
     * Record an action event.
     */
    public function recordAction(
        int $actorId,
        string $actorType,
        string $action,
        int $subjectId,
        string $subjectType,
        ?array $metadata = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ActionStat {
        return $this->repository->logAction([
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action' => $action,
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Record a public action (no auth required) with duplicate prevention.
     */
    public function recordPublicAction(
        string $action,
        int $subjectId,
        string $subjectType,
        string $ipAddress,
        ?string $userAgent = null,
        ?array $metadata = null,
        int $cooldownMinutes = 60
    ): ?ActionStat {
        // Check for duplicate within cooldown period
        $exists = ActionStat::where('action', $action)
            ->where('subject_id', $subjectId)
            ->where('subject_type', $subjectType)
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinutes($cooldownMinutes))
            ->exists();

        if ($exists) {
            return null; // Duplicate, skip
        }

        return $this->repository->logAction([
            'actor_id' => 0, // Anonymous
            'actor_type' => 'guest',
            'action' => $action,
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Get today vs yesterday comparison.
     */
    public function getTodayVsYesterdayComparison(
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayTotal = $this->repository->getTotalForDate($today, $action, $subjectId, $subjectType);
        $yesterdayTotal = $this->repository->getTotalForDate($yesterday, $action, $subjectId, $subjectType);

        return $this->buildComparisonResult($todayTotal, $yesterdayTotal, $today, $yesterday);
    }

    /**
     * Get comparison between two dates.
     */
    public function getDateComparison(
        string $date1,
        string $date2,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $date1Total = $this->repository->getTotalForDate($date1, $action, $subjectId, $subjectType);
        $date2Total = $this->repository->getTotalForDate($date2, $action, $subjectId, $subjectType);

        return $this->buildComparisonResult($date1Total, $date2Total, $date1, $date2);
    }

    /**
     * Build comparison result with percentage change.
     */
    protected function buildComparisonResult(int $currentTotal, int $previousTotal, string $currentDate, string $previousDate): array
    {
        $percentageChange = $this->calculatePercentageChange($currentTotal, $previousTotal);

        return [
            'current' => [
                'date' => $currentDate,
                'total' => $currentTotal,
            ],
            'previous' => [
                'date' => $previousDate,
                'total' => $previousTotal,
            ],
            'change' => [
                'absolute' => $currentTotal - $previousTotal,
                'percentage' => $percentageChange,
                'trend' => $this->getTrend($percentageChange),
            ],
        ];
    }

    /**
     * Calculate percentage change between two values.
     */
    protected function calculatePercentageChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get trend direction based on percentage change.
     */
    protected function getTrend(float $percentageChange): string
    {
        if ($percentageChange > 0) {
            return 'up';
        } elseif ($percentageChange < 0) {
            return 'down';
        }

        return 'stable';
    }

    /**
     * Get daily aggregated stats for a date range.
     */
    public function getDailyStats(
        string $startDate,
        string $endDate,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): Collection {
        return $this->repository->getSummaryByDateRange(
            $startDate,
            $endDate,
            $action,
            $subjectId,
            $subjectType
        );
    }

    /**
     * Get daily stats with comparison to previous period.
     */
    public function getDailyStatsWithComparison(
        string $startDate,
        string $endDate,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end) + 1;

        // Previous period
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1);

        // Current period stats
        $currentStats = $this->repository->getSummaryByDateRange(
            $startDate,
            $endDate,
            $action,
            $subjectId,
            $subjectType
        );

        // Previous period stats
        $previousStats = $this->repository->getSummaryByDateRange(
            $prevStart->toDateString(),
            $prevEnd->toDateString(),
            $action,
            $subjectId,
            $subjectType
        );

        // Calculate totals
        $currentTotal = $currentStats->sum('count');
        $previousTotal = $previousStats->sum('count');

        return [
            'stats' => $currentStats,
            'summary' => [
                'current' => [
                    'period' => ['start' => $startDate, 'end' => $endDate],
                    'total' => $currentTotal,
                ],
                'previous' => [
                    'period' => ['start' => $prevStart->toDateString(), 'end' => $prevEnd->toDateString()],
                    'total' => $previousTotal,
                ],
                'change' => [
                    'absolute' => $currentTotal - $previousTotal,
                    'percentage' => $this->calculatePercentageChange($currentTotal, $previousTotal),
                    'trend' => $this->getTrend($this->calculatePercentageChange($currentTotal, $previousTotal)),
                ],
            ],
        ];
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
        return $this->repository->getMonthlyStats(
            $year,
            $month,
            $action,
            $subjectId,
            $subjectType
        );
    }

    /**
     * Get monthly stats with comparison to previous month.
     */
    public function getMonthlyStatsWithComparison(
        int $year,
        int $month,
        ?string $action = null,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        // Current month
        $currentDate = Carbon::create($year, $month, 1);
        $currentStats = $this->repository->getMonthlyStats($year, $month, $action, $subjectId, $subjectType);

        // Previous month
        $prevDate = $currentDate->copy()->subMonth();
        $previousStats = $this->repository->getMonthlyStats(
            $prevDate->year,
            $prevDate->month,
            $action,
            $subjectId,
            $subjectType
        );

        // Calculate totals
        $currentTotal = $currentStats->sum('total');
        $previousTotal = $previousStats->sum('total');

        return [
            'stats' => $currentStats,
            'summary' => [
                'current' => [
                    'period' => $currentDate->format('Y-m'),
                    'total' => $currentTotal,
                ],
                'previous' => [
                    'period' => $prevDate->format('Y-m'),
                    'total' => $previousTotal,
                ],
                'change' => [
                    'absolute' => $currentTotal - $previousTotal,
                    'percentage' => $this->calculatePercentageChange($currentTotal, $previousTotal),
                    'trend' => $this->getTrend($this->calculatePercentageChange($currentTotal, $previousTotal)),
                ],
            ],
        ];
    }

    /**
     * Get stats breakdown by action type.
     */
    public function getStatsByAction(
        string $startDate,
        string $endDate,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $aggregated = $this->repository->getAggregatedByAction(
            $startDate,
            $endDate,
            $subjectId,
            $subjectType
        );

        $result = [];
        foreach (ActionType::values() as $action) {
            $item = $aggregated->firstWhere('action', $action);
            $result[$action] = $item ? (int) $item->total : 0;
        }

        $result['total'] = array_sum($result);

        return $result;
    }

    /**
     * Get stats summary for profile owner (my stats).
     */
    public function getMyStatsSummary(
        int $subjectId,
        string $subjectType,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $action = null
    ): array {
        $endDate = $endDate ?? now()->toDateString();
        $startDate = $startDate ?? now()->subDays(30)->toDateString();

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end) + 1;

        // Previous period
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1);

        // Current period stats
        $currentStats = $this->repository->getSummaryByDateRange(
            $startDate,
            $endDate,
            $action,
            $subjectId,
            $subjectType
        );

        // Previous period stats
        $previousStats = $this->repository->getSummaryByDateRange(
            $prevStart->toDateString(),
            $prevEnd->toDateString(),
            $action,
            $subjectId,
            $subjectType
        );

        // Group current stats by action
        $currentByAction = [];
        $previousByAction = [];

        foreach (ActionType::values() as $actionType) {
            $currentByAction[$actionType] = $currentStats->where('action', $actionType)->sum('count');
            $previousByAction[$actionType] = $previousStats->where('action', $actionType)->sum('count');
        }

        // Build result with comparison for each action
        $result = [];
        foreach (ActionType::values() as $actionType) {
            if ($action && $action !== $actionType) {
                continue;
            }

            $current = (int) $currentByAction[$actionType];
            $previous = (int) $previousByAction[$actionType];

            $result[$actionType] = [
                'current' => $current,
                'previous' => $previous,
                'change' => [
                    'absolute' => $current - $previous,
                    'percentage' => $this->calculatePercentageChange($current, $previous),
                    'trend' => $this->getTrend($this->calculatePercentageChange($current, $previous)),
                ],
            ];
        }

        // Total
        $currentTotal = array_sum($currentByAction);
        $previousTotal = array_sum($previousByAction);

        if (!$action) {
            $result['total'] = [
                'current' => $currentTotal,
                'previous' => $previousTotal,
                'change' => [
                    'absolute' => $currentTotal - $previousTotal,
                    'percentage' => $this->calculatePercentageChange($currentTotal, $previousTotal),
                    'trend' => $this->getTrend($this->calculatePercentageChange($currentTotal, $previousTotal)),
                ],
            ];
        }

        return [
            'stats' => $result,
            'period' => [
                'current' => ['start' => $startDate, 'end' => $endDate],
                'previous' => ['start' => $prevStart->toDateString(), 'end' => $prevEnd->toDateString()],
            ],
        ];
    }

    /**
     * Get overview stats for profile cards.
     * Returns: Total all-time count + Today vs Yesterday comparison
     */
    public function getOverviewStats(
        int $subjectId,
        string $subjectType
    ): array {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // 1. Get TOTAL all-time count for each action
        $totalStats = $this->repository->getTotalBySubject($subjectId, $subjectType);

        // 2. Get TODAY's count for each action
        $todayStats = $this->repository->getSummaryByDateRange(
            $today,
            $today,
            null,
            $subjectId,
            $subjectType
        );

        // 3. Get YESTERDAY's count for each action
        $yesterdayStats = $this->repository->getSummaryByDateRange(
            $yesterday,
            $yesterday,
            null,
            $subjectId,
            $subjectType
        );

        // Group by action
        $totalByAction = [];
        $todayByAction = [];
        $yesterdayByAction = [];

        foreach (ActionType::values() as $actionType) {
            $totalByAction[$actionType] = (int) $totalStats->where('action', $actionType)->sum('count');
            $todayByAction[$actionType] = (int) $todayStats->where('action', $actionType)->sum('count');
            $yesterdayByAction[$actionType] = (int) $yesterdayStats->where('action', $actionType)->sum('count');
        }


        // Build result
        $result = [];
        foreach (ActionType::values() as $actionType) {
            $total = $totalByAction[$actionType];
            $todayCount = $todayByAction[$actionType];
            $yesterdayCount = $yesterdayByAction[$actionType];
            $diff = $todayCount - $yesterdayCount;

            $result[$actionType] = [
                'total' => $total,
                'today' => $todayCount,
                'yesterday' => $yesterdayCount,
                'change' => [
                    'absolute' => $diff,
                    'percentage' => $this->calculatePercentageChange($todayCount, $yesterdayCount),
                    'trend' => $this->getTrend($this->calculatePercentageChange($todayCount, $yesterdayCount)),
                ],
            ];
        }

        // Grand total
        $grandTotal = array_sum($totalByAction);
        $grandToday = array_sum($todayByAction);
        $grandYesterday = array_sum($yesterdayByAction);
        $grandDiff = $grandToday - $grandYesterday;

        $result['total'] = [
            'total' => $grandTotal,
            'today' => $grandToday,
            'yesterday' => $grandYesterday,
            'change' => [
                'absolute' => $grandDiff,
                'percentage' => $this->calculatePercentageChange($grandToday, $grandYesterday),
                'trend' => $this->getTrend($this->calculatePercentageChange($grandToday, $grandYesterday)),
            ],
        ];

        return [
            'stats' => $result,
            'dates' => [
                'today' => $today,
                'yesterday' => $yesterday,
            ],
        ];
    }

    /**
     * Get dashboard summary for a subject.
     */
    public function getSubjectDashboard(
        int $subjectId,
        string $subjectType
    ): array {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = now()->subMonth()->endOfMonth()->toDateString();

        // Today vs Yesterday comparison for each action
        $comparisons = [];
        foreach (ActionType::values() as $action) {
            $comparisons[$action] = $this->getTodayVsYesterdayComparison(
                $action,
                $subjectId,
                $subjectType
            );
        }

        // This month totals
        $thisMonthStats = $this->getStatsByAction(
            $startOfMonth,
            $endOfMonth,
            $subjectId,
            $subjectType
        );

        // Last month totals
        $lastMonthStats = $this->getStatsByAction(
            $startOfLastMonth,
            $endOfLastMonth,
            $subjectId,
            $subjectType
        );

        // Monthly comparison
        $monthlyComparison = $this->buildComparisonResult(
            $thisMonthStats['total'],
            $lastMonthStats['total'],
            now()->format('Y-m'),
            now()->subMonth()->format('Y-m')
        );

        return [
            'daily_comparison' => $comparisons,
            'this_month' => $thisMonthStats,
            'last_month' => $lastMonthStats,
            'monthly_comparison' => $monthlyComparison,
        ];
    }

    /**
     * Get top performing subjects.
     */
    public function getTopSubjects(
        string $startDate,
        string $endDate,
        string $subjectType,
        ?string $action = null,
        int $limit = 10
    ): Collection {
        return $this->repository->getTopSubjects(
            $startDate,
            $endDate,
            $subjectType,
            $action,
            $limit
        );
    }

    /**
     * Get chart data for daily stats.
     */
    public function getChartData(
        string $startDate,
        string $endDate,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $stats = $this->getDailyStats($startDate, $endDate, null, $subjectId, $subjectType);

        // Group by date (convert Carbon to string for proper key matching)
        $grouped = $stats->groupBy(fn($item) => $item->date->toDateString());

        // Build chart-friendly data structure
        $labels = [];
        $datasets = [];
        foreach (ActionType::values() as $action) {
            $datasets[$action] = [];
        }

        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            $dateString = $currentDate->toDateString();
            $labels[] = $dateString;

            $dayStats = $grouped->get($dateString, collect());

            foreach (ActionType::values() as $action) {
                $stat = $dayStats->firstWhere('action', $action);
                $datasets[$action][] = $stat ? $stat->count : 0;
            }

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Get weekly chart data (aggregated by week).
     */
    public function getWeeklyChartData(
        int $weeks = 12,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $endDate = now()->endOfWeek();
        $startDate = now()->subWeeks($weeks - 1)->startOfWeek();

        $stats = $this->repository->getSummaryByDateRange(
            $startDate->toDateString(),
            $endDate->toDateString(),
            null,
            $subjectId,
            $subjectType
        );

        // Group by week
        $grouped = $stats->groupBy(function ($item) {
            return Carbon::parse($item->date)->startOfWeek()->toDateString();
        });

        $labels = [];
        $datasets = [];
        foreach (ActionType::values() as $action) {
            $datasets[$action] = [];
        }

        $currentWeek = $startDate->copy();
        while ($currentWeek->lte($endDate)) {
            $weekStart = $currentWeek->toDateString();
            $weekEnd = $currentWeek->copy()->endOfWeek()->toDateString();
            $labels[] = $weekStart;

            $weekStats = $grouped->get($weekStart, collect());

            foreach (ActionType::values() as $action) {
                $total = $weekStats->where('action', $action)->sum('count');
                $datasets[$action][] = (int) $total;
            }

            $currentWeek->addWeek();
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'weeks' => $weeks,
            ],
        ];
    }

    /**
     * Get monthly chart data (aggregated by month).
     */
    public function getMonthlyChartData(
        int $months = 12,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): array {
        $endDate = now()->endOfMonth();
        $startDate = now()->subMonths($months - 1)->startOfMonth();

        $stats = $this->repository->getSummaryByDateRange(
            $startDate->toDateString(),
            $endDate->toDateString(),
            null,
            $subjectId,
            $subjectType
        );

        // Group by month
        $grouped = $stats->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('Y-m');
        });

        $labels = [];
        $datasets = [];
        foreach (ActionType::values() as $action) {
            $datasets[$action] = [];
        }

        $currentMonth = $startDate->copy();
        while ($currentMonth->lte($endDate)) {
            $monthKey = $currentMonth->format('Y-m');
            $labels[] = $monthKey;

            $monthStats = $grouped->get($monthKey, collect());

            foreach (ActionType::values() as $action) {
                $total = $monthStats->where('action', $action)->sum('count');
                $datasets[$action][] = (int) $total;
            }

            $currentMonth->addMonth();
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'period' => [
                'start' => $startDate->format('Y-m'),
                'end' => $endDate->format('Y-m'),
                'months' => $months,
            ],
        ];
    }

    /**
     * Get combined chart data (both weekly and monthly).
     */
    public function getCombinedChartData(
        ?int $subjectId = null,
        ?string $subjectType = null,
        int $weeks = 12,
        int $months = 12
    ): array {
        return [
            'weekly' => $this->getWeeklyChartData($weeks, $subjectId, $subjectType),
            'monthly' => $this->getMonthlyChartData($months, $subjectId, $subjectType),
        ];
    }

    public function timeseries(
        int $subjectId,
        string $subjectType,
        Carbon $start,
        Carbon $end,
        string $groupBy
    ): array {
        return match ($groupBy) {
            'hour'  => $this->byHour($subjectId, $subjectType, $start),
            'day'   => $this->byDay($subjectId, $subjectType, $start, $end),
            'week'  => $this->byWeek($subjectId, $subjectType, $start, $end),
            'month' => $this->byMonth($subjectId, $subjectType, $start, $end),
            default => throw new \InvalidArgumentException('Invalid group_by'),
        };
    }

    /* =========================================================
       HOURLY (Today)
    ========================================================= */
    protected function byHour(int $subjectId, string $subjectType, Carbon $day): array
    {
        // ใช้ Bangkok time โดยตรง (ไม่ต้อง convert เป็น UTC)
        $start = $day->copy()->startOfDay();
        $end   = $day->copy()->endOfDay();

        $labels = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
        }

        $result = $this->emptySeries(24);

        foreach ($this->actions as $action) {
            $rows = DB::table('action_stats')
                ->selectRaw("
                    HOUR(created_at) as h,
                    COUNT(*) as total
                ")
                ->where('subject_id', $subjectId)
                ->where('subject_type', $subjectType)
                ->where('action', $action)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('h')
                ->pluck('total', 'h');

            foreach ($rows as $hour => $count) {
                $result[$action][$hour] = $count;
            }
        }

        return [
            'labels' => $labels,
            ...$result,
        ];
    }

    /* =========================================================
       DAILY
    ========================================================= */
    protected function byDay(int $subjectId, string $subjectType, Carbon $start, Carbon $end): array
    {
        $period = [];
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $period[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $result = $this->emptySeries(count($period));

        foreach ($this->actions as $action) {
            $rows = DB::table('action_stat_summaries')
                ->where('subject_id', $subjectId)
                ->where('subject_type', $subjectType)
                ->where('action', $action)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->pluck('count', 'date');

            foreach ($period as $i => $date) {
                $result[$action][$i] = $rows[$date] ?? 0;
            }
        }

        return [
            'labels' => $period,
            ...$result,
        ];
    }

    /* =========================================================
       WEEKLY
    ========================================================= */
    protected function byWeek(int $subjectId, string $subjectType, Carbon $start, Carbon $end): array
    {
        return $this->byDay($subjectId, $subjectType, $start, $end);
    }

    /* =========================================================
       MONTHLY - Group by month (12 months)
    ========================================================= */
    protected function byMonth(int $subjectId, string $subjectType, Carbon $start, Carbon $end): array
    {
        // สร้าง labels เป็นเดือน (Y-m format)
        $period = [];
        $cursor = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->startOfMonth();

        while ($cursor <= $endMonth) {
            $period[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $result = $this->emptySeries(count($period));

        foreach ($this->actions as $action) {
            // Query group by month
            $rows = DB::table('action_stat_summaries')
                ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(count) as total")
                ->where('subject_id', $subjectId)
                ->where('subject_type', $subjectType)
                ->where('action', $action)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('month')
                ->pluck('total', 'month');

            foreach ($period as $i => $month) {
                $result[$action][$i] = (int) ($rows[$month] ?? 0);
            }
        }

        return [
            'labels' => $period,
            ...$result,
        ];
    }

    /* =========================================================
       Helpers
    ========================================================= */
    protected function emptySeries(int $length): array
    {
        return [
            'profile_view'  => array_fill(0, $length, 0),
            'click_call'    => array_fill(0, $length, 0),
            'click_contact' => array_fill(0, $length, 0),
        ];
    }

}
