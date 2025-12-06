<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NursingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            // fields เฉพาะที่ต้องการ — ไม่คืน cardid ถ้าไม่เอา
            'plan' => $this->plan,
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'gender' => $this->profile->gender ?? null,
                    'address' => $this->profile->address ?? null,
                ];
            }),
            'cover_image' => $this->whenLoaded('coverImage', function () {
                return $this->coverImage?->full_path ?? null;
            }),
        ];
    }
}
