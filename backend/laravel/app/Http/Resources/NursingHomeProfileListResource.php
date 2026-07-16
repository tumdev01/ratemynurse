<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class NursingHomeProfileListResource extends JsonResource {
    public function toArray($request)
    {
        $ratingStats = $this->calculateRatingStatistics();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'certified' => $this->certified,
            'cost_per_month' => $this->cost_per_month,
            'province'   => $this->whenLoaded('province', function () {
                return [
                    'id' => $this->province->id,
                    'name' => $this->province->name
                ];
            }),
            'district'   => $this->whenLoaded('district', function() {
                return [
                    'id' => $this->district->id,
                    'name' => $this->district->name
                ];
            }),
            'rates' => $this->whenLoaded('rates'),
            // ⬇️ Rating Statistics
            'rating_avg' => $this->when(
                $this->relationLoaded('rates'),
                $ratingStats['avg']
            ),

            'rating_percentage' => $this->when(
                $this->relationLoaded('rates'),
                $ratingStats['percentage']
            ),
            
            'review_count' => $this->when(
                $this->relationLoaded('rates'),
                $ratingStats['count']
            ),

            'star_percentages' => $this->when(
                $this->relationLoaded('rates'),
                $ratingStats['star_percentages']
            ),
            'coverImage' => $this->whenLoaded('coverImage', function () {
                return $this->coverImage?->full_path ?? null;
            }),
        ];
    }

    /**
     * คำนวณสถิติ rating สำหรับ star review
     */
    protected function calculateRatingStatistics(): array
    {
        $stats = [
            'avg' => 0,
            'percentage' => 0,
            'count' => 0,
            'star_percentages' => [],
        ];

        if (!$this->relationLoaded('rates')) {
            return $stats;
        }

        $rateDetails = $this->rates->flatMap->rate_details;
        $stats['count'] = $this->rates->count();

        if ($rateDetails->isEmpty()) {
            // ถ้าไม่มีรีวิว ให้ return เปอร์เซ็นต์ 0 ทั้งหมด
            $stats['star_percentages'] = [
                ['stars' => 5, 'percentage' => 0, 'count' => 0],
                ['stars' => 4, 'percentage' => 0, 'count' => 0],
                ['stars' => 3, 'percentage' => 0, 'count' => 0],
                ['stars' => 2, 'percentage' => 0, 'count' => 0],
                ['stars' => 1, 'percentage' => 0, 'count' => 0],
            ];
            return $stats;
        }

        // คำนวณค่าเฉลี่ย
        $avgRating = $rateDetails->avg('scores');
        $stats['avg'] = round($avgRating, 1);
        
        // คำนวณ percentage ของค่าเฉลี่ย (จาก 5 คะแนน = 100%)
        $stats['percentage'] = round(($avgRating / 5) * 100, 1);

        // นับจำนวนแต่ละระดับดาว
        $total = $rateDetails->count();
        $grouped = $rateDetails->groupBy(function($detail) {
            // ปัดเศษคะแนนเป็นจำนวนเต็ม (4.5 → 5, 4.4 → 4)
            return (int) round($detail->scores);
        });

        // สร้าง array สำหรับแสดงผล
        $starPercentages = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $count = $grouped->get($stars, collect())->count();
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            
            $starPercentages[] = [
                'stars' => $stars,
                'percentage' => $percentage,
                'count' => $count,
            ];
        }

        $stats['star_percentages'] = $starPercentages;

        return $stats;
    }
}