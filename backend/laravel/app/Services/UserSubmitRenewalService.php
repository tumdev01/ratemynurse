<?php

namespace App\Services;

use App\Models\UserSubscription;
use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use Carbon\Carbon;

class UserSubmitRenewalService
{
    public function submit(int $userId, int $profileId, string $type, string $plan): UserSubscriptionRequest
    {
        $request = UserSubscriptionRequest::updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
            ],
            [
                'profile_id' => $profileId,
                'plan' => $plan,
                'status' => 'awaiting_payment',
            ]
        );

        $this->log($request, 'submitted', 'user');
        $this->log($request, 'awaiting_payment', 'user');

        return $request;
    }

    public function acceptPayment(int $requestId, int $adminId): UserSubscriptionRequest
    {
        $request = UserSubscriptionRequest::findOrFail($requestId);

        // Soft delete existing subscription for this profile
        UserSubscription::where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->delete();

        // Create new subscription
        UserSubscription::create([
            'subscribable_id' => $request->profile_id,
            'subscribable_type' => $request->type,
            'plan' => $request->plan,
            'start_date' => Carbon::now(),
        ]);

        $request->update(['status' => 'payment_accepted']);

        $this->log($request, 'payment_accepted', 'admin', $adminId);

        return $request;
    }

    public function getIncomingRequests()
    {
        return UserSubscriptionRequest::where('status', 'awaiting_payment')
            ->with(['user', 'logs'])
            ->latest()
            ->get();
    }

    public function getRequestWithHistory(int $requestId): array
    {
        $request = UserSubscriptionRequest::with(['user', 'logs' => fn ($q) => $q->orderBy('created_at')])
            ->findOrFail($requestId);

        // All logs for this user across all subscription requests
        $allLogs = UserSubscriptionLog::where('user_id', $request->user_id)
            ->with('request')
            ->orderByDesc('created_at')
            ->get();

        // Current active subscription
        $currentSubscription = UserSubscription::where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->latest()
            ->first();

        // Past subscriptions (soft-deleted)
        $pastSubscriptions = UserSubscription::onlyTrashed()
            ->where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->latest()
            ->get();

        return [
            'request' => $request,
            'allLogs' => $allLogs,
            'currentSubscription' => $currentSubscription,
            'pastSubscriptions' => $pastSubscriptions,
        ];
    }

    protected function log(UserSubscriptionRequest $request, string $action, string $performedBy, ?int $adminId = null): void
    {
        UserSubscriptionLog::create([
            'subscription_request_id' => $request->id,
            'user_id' => $adminId ?? $request->user_id,
            'action' => $action,
            'performed_by' => $performedBy,
        ]);
    }
}
