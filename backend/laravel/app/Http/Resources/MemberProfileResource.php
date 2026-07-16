<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        
        // Add relationships
        $data['subscriptions'] = UserSubscriptionResource::collection(
            $this->whenLoaded('subscriptions')
        );
        
        $data['current_active_subscription'] = $this->whenLoaded('currentActiveSubscription')
            ? new UserSubscriptionResource($this->currentActiveSubscription)
            : null;
            
        // Add location data
        $data['province'] = $this->whenLoaded('province', function() {
            return [
                'id' => $this->province->id,
                'name' => $this->province->name ?? $this->province->name_th ?? null,
            ];
        });
        
        $data['district'] = $this->whenLoaded('district', function() {
            return [
                'id' => $this->district->id,
                'name' => $this->district->name ?? $this->district->name_th ?? null,
            ];
        });
        
        $data['subDistrict'] = $this->whenLoaded('subDistrict', function() {
            return [
                'id' => $this->subDistrict->id,
                'name' => $this->subDistrict->name ?? $this->subDistrict->name_th ?? null,
            ];
        });

        // แก้ key จาก cover_image → coverImage
        $data['coverImage'] = $this->whenLoaded('coverImage', function() {
            return $this->coverImage?->full_path;
        });
        unset($data['cover_image']);

        return $data;
    }
}