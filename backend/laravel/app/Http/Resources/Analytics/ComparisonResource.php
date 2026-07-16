<?php

namespace App\Http\Resources\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComparisonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'current' => [
                'date' => $this->resource['current']['date'],
                'total' => $this->resource['current']['total'],
            ],
            'previous' => [
                'date' => $this->resource['previous']['date'],
                'total' => $this->resource['previous']['total'],
            ],
            'change' => [
                'absolute' => $this->resource['change']['absolute'],
                'percentage' => $this->resource['change']['percentage'],
                'trend' => $this->resource['change']['trend'],
            ],
        ];
    }
}
