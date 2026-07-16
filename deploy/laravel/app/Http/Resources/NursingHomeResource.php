<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NursingHomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // ===== User level ===== ชื่อ-นามสกุลของ "เจ้าของบัญชี" (users table) — ต่างจาก Nursing/Member
            // ตรงที่ NursingHome หนึ่ง user อาจมีหลายสาขา (nursing_home_profiles) เชื่อมด้วย user_id
            // จึงต้องใช้ firstname/lastname จาก user เป็นชื่อที่แสดงในเมนู ไม่ใช่ชื่อสาขา (profile.name)
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'plan' => $this->plan,
            'plan_start' => $this->plan_start,

            // ===== Profiles (พหูพจน์ — หนึ่ง user มีได้หลายสาขา) =====
            'profiles' => $this->whenLoaded('profiles', function () use ($request) {
                return $this->profiles->map(function ($profile) use ($request) {
                    return [
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'about' => $profile->about ?? null,
                        'address' => $profile->address ?? null,
                        'province' => $profile->province?->name ?? null,
                        'district' => $profile->district?->name ?? null,
                        'subDistrict' => $profile->subDistrict?->name ?? null,
                        'coverImage' => $profile->coverImage?->full_path ?? null,
                        'is_favorite' => $profile->isFavoritedBy($request->user()),
                    ];
                });
            }),

            // ===== Notifications (ระดับ user เดียวกับ Member/Nursing) =====
            'notifications' => $this->whenLoaded('notifications'),
            'read_notifications' => $this->whenLoaded('readNotifications'),
            'unread_notifications' => $this->whenLoaded('unreadNotifications'),

            // ===== Extra relations (คงไว้เผื่อจุดอื่นยังใช้อยู่) =====
            'staffs_count' => $this->whenLoaded('staffs', fn () => $this->staffs->count()),
            'rooms_count'  => $this->whenLoaded('rooms', fn () => $this->rooms->count()),
        ];
    }
}
