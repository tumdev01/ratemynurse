<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NursingHomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // ===== User level =====
            'id' => $this->id,
            'user_type' => $this->user_type,

            // ===== Profile =====
            'profile' => $this->whenLoaded('profile', function () use ($request) {
                return [
                    'id' => $this->profile->id,
                    'name' => $this->profile->name,
                    'about' => $this->profile->about ?? null,
                    'address' => $this->profile->address ?? null,

                    // ⭐ favorite อยู่ที่ profile
                    'is_favorite' => $this->profile->isFavoritedBy($request->user()),
                ];
            }),

            // ===== Images =====
            'cover_image' => $this->whenLoaded('coverImage', function () {
                return [
                    'id' => $this->coverImage->id,
                    'path' => $this->coverImage->path,
                ];
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                ]);
            }),

            // ===== Extra relations =====
            'staffs_count' => $this->whenLoaded('staffs', fn () => $this->staffs->count()),
            'rooms_count'  => $this->whenLoaded('rooms', fn () => $this->rooms->count()),
            'rates_avg'    => $this->whenLoaded('rates', fn () => round($this->rates->avg('score'), 1)),
        ];
    }
}
