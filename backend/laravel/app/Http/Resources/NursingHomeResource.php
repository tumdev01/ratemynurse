<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NursingHomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname'  => $this->lastname,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'user_type' => $this->user_type,
            'plan' => $this->plan,

            // เดิม: profile (hasOne) → dump ทุก column + whenLoaded dot notation ไม่ทำงาน
            // 'profile' => $this->whenLoaded('profile'),
            // 'profile.province:id,name' => $this->whenLoaded('profile.province', function () {
            //     return $this->profile->province?->name ?? null;
            // }),
            // 'profile.district:id,name' => $this->whenLoaded('profile.district', function () {
            //     return $this->profile->district?->name ?? null;
            // }),
            // 'profile.subDistrict:id,name' => $this->whenLoaded('profile.subDistrict', function () {
            //     return $this->profile->subDistrict?->name ?? null;
            // }),

            // ใหม่: profiles (hasMany) → เลือกเฉพาะ field ที่ต้องการ + coverImage ระดับ profile
            'profiles' => $this->whenLoaded('profiles', function () {
                return $this->profiles->map(function ($profile) {
                    return [
                        'id'   => $profile->id,
                        'name' => $profile->name,
                        'province'    => $profile->province?->name ?? null,
                        'district'    => $profile->district?->name ?? null,
                        'subDistrict' => $profile->subDistrict?->name ?? null,
                        'coverImage'  => $profile->coverImage?->full_path ?? null,
                        'rates'       => $profile->relationLoaded('rates') ? $profile->rates : [],
                        'subscriptions' => $profile->relationLoaded('subscriptions') ? $profile->subscriptions : [],
                        'current_active_subscription' => $profile->relationLoaded('currentActiveSubscription')
                            ? $profile->currentActiveSubscription
                            : null,
                    ];
                });
            }),

            'notifications_count'   => $this->whenLoaded('notifications', fn () => $this->notifications->count()),
            'unread_notifications_count' => $this->whenLoaded('unreadNotifications', fn () => $this->unreadNotifications->count()),
        ];
    }
}
