<?php

namespace Database\Seeders;

use App\Models\UserSubscription;
use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // Nursing Profiles - Active Subscriptions
        // =====================================================================

        // Nursing #1 (user_id=3) - PROFESSIONAL plan, active
        $req1 = UserSubscriptionRequest::create([
            'user_id' => 3,
            'profile_id' => 1,
            'type' => 'App\Models\NursingProfile',
            'plan' => 'PROFESSIONAL',
            'status' => 'payment_accepted',
        ]);

        UserSubscription::create([
            'subscribable_id' => 1,
            'subscribable_type' => 'App\Models\NursingProfile',
            'plan' => 'PROFESSIONAL',
            'start_date' => Carbon::now()->subDays(10),
        ]);

        $this->logActions($req1, 3, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 12],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 12],
            ['action' => 'payment_accepted', 'performed_by' => 'admin', 'admin_id' => 1, 'days_ago' => 10],
        ]);

        // Nursing #2 (user_id=4) - BASIC plan, active
        $req2 = UserSubscriptionRequest::create([
            'user_id' => 4,
            'profile_id' => 2,
            'type' => 'App\Models\NursingProfile',
            'plan' => 'BASIC',
            'status' => 'payment_accepted',
        ]);

        UserSubscription::create([
            'subscribable_id' => 2,
            'subscribable_type' => 'App\Models\NursingProfile',
            'plan' => 'BASIC',
            'start_date' => Carbon::now()->subDays(5),
        ]);

        $this->logActions($req2, 4, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 7],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 7],
            ['action' => 'payment_accepted', 'performed_by' => 'admin', 'admin_id' => 1, 'days_ago' => 5],
        ]);

        // =====================================================================
        // Nursing #3 (user_id=5) - Awaiting payment (pending request)
        // =====================================================================

        $req3 = UserSubscriptionRequest::create([
            'user_id' => 5,
            'profile_id' => 3,
            'type' => 'App\Models\NursingProfile',
            'plan' => 'PROFESSIONAL',
            'status' => 'awaiting_payment',
        ]);

        $this->logActions($req3, 5, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 2],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 2],
        ]);

        // =====================================================================
        // Nursing Home Profiles - Active Subscriptions
        // =====================================================================

        // NursingHome #1 (user_id=13) - ENTERPRISE plan, active
        $req4 = UserSubscriptionRequest::create([
            'user_id' => 13,
            'profile_id' => 1,
            'type' => 'App\Models\NursingHomeProfile',
            'plan' => 'ENTERPRISE',
            'status' => 'payment_accepted',
        ]);

        UserSubscription::create([
            'subscribable_id' => 1,
            'subscribable_type' => 'App\Models\NursingHomeProfile',
            'plan' => 'ENTERPRISE',
            'start_date' => Carbon::now()->subDays(15),
        ]);

        $this->logActions($req4, 13, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 18],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 18],
            ['action' => 'payment_accepted', 'performed_by' => 'admin', 'admin_id' => 1, 'days_ago' => 15],
        ]);

        // NursingHome #2 (user_id=14) - PREMIUM plan, active (upgraded from BASIC)
        // Old subscription (soft deleted)
        $oldSub = UserSubscription::create([
            'subscribable_id' => 2,
            'subscribable_type' => 'App\Models\NursingHomeProfile',
            'plan' => 'BASIC',
            'start_date' => Carbon::now()->subDays(45),
        ]);
        $oldSub->delete(); // soft delete

        $req5 = UserSubscriptionRequest::create([
            'user_id' => 14,
            'profile_id' => 2,
            'type' => 'App\Models\NursingHomeProfile',
            'plan' => 'PREMIUM',
            'status' => 'payment_accepted',
        ]);

        UserSubscription::create([
            'subscribable_id' => 2,
            'subscribable_type' => 'App\Models\NursingHomeProfile',
            'plan' => 'PREMIUM',
            'start_date' => Carbon::now()->subDays(3),
        ]);

        $this->logActions($req5, 14, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 5],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 5],
            ['action' => 'payment_accepted', 'performed_by' => 'admin', 'admin_id' => 1, 'days_ago' => 3],
        ]);

        // =====================================================================
        // NursingHome #3 (user_id=15) - Awaiting payment
        // =====================================================================

        $req6 = UserSubscriptionRequest::create([
            'user_id' => 15,
            'profile_id' => 3,
            'type' => 'App\Models\NursingHomeProfile',
            'plan' => 'BASIC',
            'status' => 'awaiting_payment',
        ]);

        $this->logActions($req6, 15, [
            ['action' => 'submitted', 'performed_by' => 'user', 'days_ago' => 1],
            ['action' => 'awaiting_payment', 'performed_by' => 'user', 'days_ago' => 1],
        ]);
    }

    private function logActions(UserSubscriptionRequest $request, int $userId, array $actions): void
    {
        foreach ($actions as $action) {
            UserSubscriptionLog::create([
                'subscription_request_id' => $request->id,
                'user_id' => $action['admin_id'] ?? $userId,
                'action' => $action['action'],
                'performed_by' => $action['performed_by'],
                'created_at' => Carbon::now()->subDays($action['days_ago']),
                'updated_at' => Carbon::now()->subDays($action['days_ago']),
            ]);
        }
    }
}
