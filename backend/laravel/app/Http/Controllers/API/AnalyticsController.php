<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Http\Requests\LogActionRequest;
use App\Http\Resources\Analytics\ChartDataResource;
use App\Http\Resources\Analytics\ComparisonResource;
use App\Http\Resources\Analytics\DashboardResource;
use App\Http\Resources\Analytics\StatSummaryResource;
use App\Services\Analytics\ActionAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct(
        protected ActionAnalyticsService $analyticsService
    ) {}

    /**
     * Log an action event (authenticated).
     */
    public function logAction(LogActionRequest $request): JsonResponse
    {
        $user = $request->user();

        $actionStat = $this->analyticsService->recordAction(
            actorId: $user->id,
            actorType: get_class($user),
            action: $request->input('action'),
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type'),
            metadata: $request->input('metadata'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Action logged successfully',
            'data' => [
                'id' => $actionStat->id,
            ],
        ], 201);
    }

    /**
     * Log a public action (no auth, with duplicate prevention).
     * Supports: profile_view, click_contact, click_call
     */
    public function logPublicAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'sometimes|string|in:profile_view,click_contact,click_call',
            'subject_id' => 'required|integer',
            'subject_type' => 'required|string|in:App\\Models\\NursingProfile,App\\Models\\NursingHomeProfile',
            'metadata' => 'nullable|array',
        ]);

        $action = $validated['action'] ?? 'profile_view';

        // Cooldown: profile_view = 60 นาที, click actions = 5 นาที
        $cooldownMinutes = $action === 'profile_view' ? 60 : 5;

        $actionStat = $this->analyticsService->recordPublicAction(
            action: $action,
            subjectId: $validated['subject_id'],
            subjectType: $validated['subject_type'],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: $validated['metadata'] ?? null,
            cooldownMinutes: $cooldownMinutes
        );

        if (!$actionStat) {
            return response()->json([
                'success' => true,
                'message' => 'Already tracked',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Action tracked successfully',
            'data' => [
                'id' => $actionStat->id,
            ],
        ], 201);
    }

    /**
     * Get my profile overview stats.
     * Returns: Total all-time + Today vs Yesterday comparison
     */
    public function myOverviewStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Determine subject based on user type
        $profile = match ($user->user_type) {
            'NURSING' => \App\Models\NursingProfile::where('user_id', $user->id)->first(),
            'NURSING_HOME' => $request->input('profile_id')
                ? \App\Models\NursingHomeProfile::where('user_id', $user->id)
                    ->where('id', $request->input('profile_id'))->first()
                : \App\Models\NursingHomeProfile::where('user_id', $user->id)->first(),
            default => null,
        };

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $subjectType = match ($user->user_type) {
            'NURSING' => 'App\\Models\\NursingProfile',
            'NURSING_HOME' => 'App\\Models\\NursingHomeProfile',
            default => null,
        };

        $stats = $this->analyticsService->getOverviewStats(
            subjectId: $profile->id,
            subjectType: $subjectType
        );

        return response()->json([
            'success' => true,
            'data' => $stats['stats'],
            'dates' => $stats['dates'],
        ]);
    }

    /**
     * Get my profile stats summary.
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Determine subject based on user type
        $profile = match ($user->user_type) {
            'NURSING' => \App\Models\NursingProfile::where('user_id', $user->id)->first(),
            'NURSING_HOME' => $request->input('profile_id')
                ? \App\Models\NursingHomeProfile::where('user_id', $user->id)
                    ->where('id', $request->input('profile_id'))->first()
                : \App\Models\NursingHomeProfile::where('user_id', $user->id)->first(),
            default => null,
        };

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $subjectType = match ($user->user_type) {
            'NURSING' => 'App\\Models\\NursingProfile',
            'NURSING_HOME' => 'App\\Models\\NursingHomeProfile',
            default => null,
        };

        $stats = $this->analyticsService->getMyStatsSummary(
            subjectId: $profile->id,
            subjectType: $subjectType,
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            action: $request->input('action')
        );

        return response()->json([
            'success' => true,
            'data' => $stats['stats'],
            'period' => $stats['period'],
        ]);
    }

    /**
     * Get today vs yesterday comparison.
     */
    public function comparison(AnalyticsRequest $request): JsonResponse
    {
        $comparison = $this->analyticsService->getTodayVsYesterdayComparison(
            action: $request->input('action'),
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type')
        );

        return response()->json([
            'success' => true,
            'data' => new ComparisonResource($comparison),
        ]);
    }

    /**
     * Get daily stats for a date range with comparison.
     */
    public function dailyStats(AnalyticsRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $result = $this->analyticsService->getDailyStatsWithComparison(
            startDate: $startDate,
            endDate: $endDate,
            action: $request->input('action'),
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type')
        );

        return response()->json([
            'success' => true,
            'data' => StatSummaryResource::collection($result['stats']),
            'summary' => $result['summary'],
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Get monthly stats with comparison.
     */
    public function monthlyStats(AnalyticsRequest $request): JsonResponse
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $result = $this->analyticsService->getMonthlyStatsWithComparison(
            year: $year,
            month: $month,
            action: $request->input('action'),
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type')
        );

        return response()->json([
            'success' => true,
            'data' => $result['stats'],
            'summary' => $result['summary'],
            'meta' => [
                'year' => $year,
                'month' => $month,
            ],
        ]);
    }

    /**
     * Get stats breakdown by action.
     */
    public function statsByAction(AnalyticsRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $stats = $this->analyticsService->getStatsByAction(
            startDate: $startDate,
            endDate: $endDate,
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type')
        );

        return response()->json([
            'success' => true,
            'data' => $stats,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Get dashboard data for a subject.
     */
    public function dashboard(Request $request, int $subjectId, string $subjectType): JsonResponse
    {
        $subjectTypeMap = [
            'nursing' => 'App\\Models\\NursingProfile',
            'nursing_home' => 'App\\Models\\NursingHomeProfile',
        ];

        $resolvedSubjectType = $subjectTypeMap[$subjectType] ?? $subjectType;

        $dashboard = $this->analyticsService->getSubjectDashboard(
            subjectId: $subjectId,
            subjectType: $resolvedSubjectType
        );

        return response()->json([
            'success' => true,
            'data' => new DashboardResource($dashboard),
        ]);
    }

    /**
     * Get top performing subjects.
     */
    public function topSubjects(AnalyticsRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $subjectType = $request->input('subject_type', 'App\\Models\\NursingProfile');
        $limit = $request->input('limit', 10);

        $topSubjects = $this->analyticsService->getTopSubjects(
            startDate: $startDate,
            endDate: $endDate,
            subjectType: $subjectType,
            action: $request->input('action'),
            limit: $limit
        );

        return response()->json([
            'success' => true,
            'data' => $topSubjects,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Get chart data for visualizations.
     */
    public function chartData(AnalyticsRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $chartData = $this->analyticsService->getChartData(
            startDate: $startDate,
            endDate: $endDate,
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type')
        );

        return response()->json([
            'success' => true,
            'data' => new ChartDataResource($chartData),
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Get chart data aggregated by week and month.
     */
    public function chartSummary(AnalyticsRequest $request): JsonResponse
    {
        $weeks = $request->input('weeks', 12);
        $months = $request->input('months', 12);

        $chartData = $this->analyticsService->getCombinedChartData(
            subjectId: $request->input('subject_id'),
            subjectType: $request->input('subject_type'),
            weeks: $weeks,
            months: $months
        );

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    public function timeseries(Request $request, ActionAnalyticsService $service)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();

        // Determine profile based on user type
        $profile = match ($user->user_type) {
            'NURSING' => \App\Models\NursingProfile::where('user_id', $user->id)->first(),
            'NURSING_HOME' => $request->input('profile_id')
                ? \App\Models\NursingHomeProfile::where('user_id', $user->id)
                    ->where('id', $request->input('profile_id'))->first()
                : \App\Models\NursingHomeProfile::where('user_id', $user->id)->first(),
            default => null,
        };

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $subjectType = match ($user->user_type) {
            'NURSING' => 'App\\Models\\NursingProfile',
            'NURSING_HOME' => 'App\\Models\\NursingHomeProfile',
            default => null,
        };

        $tz = 'Asia/Bangkok';

        $start = Carbon::parse($request->start_date, $tz)->startOfDay();
        $end   = Carbon::parse($request->end_date, $tz)->endOfDay();

        $diffDays = $start->diffInDays($end);

        // Smart groupBy based on date range
        $groupBy = match (true) {
            $diffDays < 2  => 'hour',   // 1 วัน = รายชั่วโมง
            $diffDays < 8  => 'week',   // 2-7 วัน = รายวัน (แสดงชื่อวัน)
            $diffDays < 60 => 'day',    // 8-60 วัน = รายวัน (แสดงวันที่)
            default        => 'month',  // 60+ วัน = รายเดือน
        };

        $data = $service->timeseries(
            subjectId: $profile->id,
            subjectType: $subjectType,
            start: $start,
            end: $end,
            groupBy: $groupBy
        );

        return response()->json([
            'success' => true,
            'meta' => [
                'group_by' => $groupBy,
                'timezone' => $tz,
                'period' => [
                    'start' => $start->toDateString(),
                    'end'   => $end->toDateString(),
                ],
            ],
            'data' => $data,
        ]);
    }

}
