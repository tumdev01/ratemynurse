<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Enums\SubscriptionPlan;
use App\Services\UserSubmitRenewalService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionRequestController extends Controller
{
    public function __construct(
        protected UserSubmitRenewalService $service
    ) {}

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'profile_id' => 'required|integer',
            'type' => 'required|string|in:App\Models\MemberProfile,App\Models\NursingProfile,App\Models\NursingHomeProfile',
            'plan' => 'required|string',
        ]);

        $allowedPlans = SubscriptionPlan::valuesForType($validated['type']);

        if (!in_array($validated['plan'], $allowedPlans)) {
            throw ValidationException::withMessages([
                'plan' => 'Invalid plan for this profile type. Allowed: ' . implode(', ', $allowedPlans),
            ]);
        }

        $result = $this->service->submit(
            userId: $request->user()->id,
            profileId: $validated['profile_id'],
            type: $validated['type'],
            plan: $validated['plan'],
        );

        return response()->json([
            'message' => 'Subscription request submitted.',
            'data' => $result,
        ]);
    }
}
