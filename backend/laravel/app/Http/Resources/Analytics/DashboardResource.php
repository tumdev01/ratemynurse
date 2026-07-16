<?php

namespace App\Http\Resources\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dailyComparisons = [];
        foreach ($this->resource['daily_comparison'] as $action => $comparison) {
            $dailyComparisons[$action] = (new ComparisonResource($comparison))->toArray($request);
        }

        return [
            'daily_comparison' => $dailyComparisons,
            'this_month' => $this->resource['this_month'],
            'last_month' => $this->resource['last_month'],
            'monthly_comparison' => (new ComparisonResource($this->resource['monthly_comparison']))->toArray($request),
        ];
    }
}
