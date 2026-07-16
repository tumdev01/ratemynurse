<?php

namespace App\Http\Resources\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'subject_id' => $this->subject_id,
            'subject_type' => $this->subject_type,
            'date' => $this->date->format('Y-m-d'),
            'count' => $this->count,
        ];
    }
}
