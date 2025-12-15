<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NursingCvResource;
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
                        'is_cover' => $image->is_cover,
                    ];
                });
            }),
            'cvs' => $this->whenLoaded('cvs', function () {
                return new NursingCvResource($this->cvs);
            }),
            'rates' => $this->whenLoaded('rates'),
            'costs' => $this->whenLoaded('costs'),
            'detail' => $this->whenLoaded('detail'),
            'cover_image' => $this->whenLoaded('coverImage', function () {
                return $this->coverImage?->full_path ?? null;
            }),
        ];
    }
}
