<?php
namespace App\Repositories;

use App\Models\JobInterview;

class JobInterviewRepository extends BaseRepository {
    public function applyJob(array $input): JobInterview
    {
        return JobInterview::create($input);
    }

    public function getInterviews(int $userId, array $filters = [])
    {
        $query = JobInterview::select('id', 'job_id', 'user_id', 'class_type', 'profile_id', 'price', 'start_date', 'created_at')
            ->with([
                'job:id,name,status,service_type,care_type,hire_type,hire_rule,province_id,district_id',
                'job.province:id,name',
                'job.district:id,name',
                'nursingProfile:id,name',
                'nursingHomeProfile:id,name',
            ])
            ->where('user_id', $userId);

        if (!empty($filters['service_type'])) {
            $query->whereHas('job', fn($q) => $q->where('service_type', $filters['service_type']));
        }

        if (!empty($filters['care_type'])) {
            $query->whereHas('job', fn($q) => $q->where('care_type', $filters['care_type']));
        }

        if (!empty($filters['hire_rule'])) {
            $query->whereHas('job', fn($q) => $q->where('hire_rule', $filters['hire_rule']));
        }

        if (!empty($filters['province_id'])) {
            $query->whereHas('job', fn($q) => $q->where('province_id', $filters['province_id']));
        }

        if (!empty($filters['min_cost'])) {
            $query->where('price', '>=', $filters['min_cost']);
        }

        if (!empty($filters['max_cost'])) {
            $query->where('price', '<=', $filters['max_cost']);
        }

        if (!empty($filters['created_at'])) {
            $dates = explode(',', $filters['created_at']);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [$dates[0], $dates[1] . ' 23:59:59']);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        $limits = $filters['limits'] ?? 10;

        return $query->orderBy('created_at', 'DESC')
            ->paginate($limits);
    }

    public function destroy(int $id, int $userId): array
    {
        $interview = JobInterview::with('job')->find($id);

        if (!$interview) {
            return ['success' => false, 'message' => 'ไม่พบรายการสมัครงาน', 'code' => 404];
        }

        if ($interview->job->user_id !== $userId) {
            return ['success' => false, 'message' => 'คุณไม่มีสิทธิ์ลบรายการนี้', 'code' => 403];
        }

        $interview->delete();

        return ['success' => true, 'message' => 'ลบรายการสมัครงานเรียบร้อยแล้ว', 'code' => 200];
    }
}