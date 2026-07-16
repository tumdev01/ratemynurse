<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NursingCvResource;

class NursingResource extends JsonResource
{
    public function toArray($request)
    {
        $ratingStats = $this->calculateRatingStatistics();

        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'plan' => $this->plan,
            
            'profile' => $this->whenLoaded('profile'),
            
            'profile.province:id,name' => $this->whenLoaded('profile.province', function () {
                return $this->profile->province?->name ?? null;
            }),
            
            'profile.district:id,name' => $this->whenLoaded('profile.district', function () {
                return $this->profile->district?->name ?? null;
            }),
            
            'profile.subDistrict:id,name' => $this->whenLoaded('profile.subDistrict', function () {
                return $this->profile->subDistrict?->name ?? null;
            }),
            
            'images:id,user_id,path,is_cover' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'user_id' => $image->user_id,
                        'path' => $image->path,
                        'full_path' => $image->full_path ?? null,
                        'is_cover' => $image->is_cover,
                    ];
                });
            }),
            
            'cvs' => $this->whenLoaded('cvs', function () {
                return new NursingCvResource($this->cvs);
            }),
            
            'rates' => $this->whenLoaded('rates'),
            'costs' => $this->whenLoaded('costs'),
            
            'lowest_price' => $this->whenLoaded('lowestCost', function () {
                return $this->lowestCost ? [
                    'cost' => (float) $this->lowestCost->cost,
                    'type' => $this->lowestCost->type,
                ] : null;
            }),
            
            'detail' => $this->whenLoaded('detail'),
            
            'coverImage' => $this->whenLoaded('coverImage', function () {
                return $this->coverImage?->full_path ?? null;
            }),

            // ⬇️ Rating Statistics
            'rating_avg' => $this->when(
                $this->relationLoaded('profile') && $this->profile?->relationLoaded('rates'),
                $ratingStats['avg']
            ),

            'rating_percentage' => $this->when(
                $this->relationLoaded('profile') && $this->profile?->relationLoaded('rates'),
                $ratingStats['percentage']
            ),
            
            'review_count' => $this->when(
                $this->relationLoaded('profile') && $this->profile?->relationLoaded('rates'),
                $ratingStats['count']
            ),

            'star_percentages' => $this->when(
                $this->relationLoaded('profile') && $this->profile?->relationLoaded('rates'),
                $ratingStats['star_percentages']
            ),
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

        if (!$this->relationLoaded('profile') || !$this->profile?->relationLoaded('rates')) {
            return $stats;
        }

        $rateDetails = $this->profile->rates->flatMap->rate_details;
        $stats['count'] = $this->profile->rates->count();

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