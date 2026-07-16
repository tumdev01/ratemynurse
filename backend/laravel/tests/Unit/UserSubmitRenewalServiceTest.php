<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserSubscription;
use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use App\Services\UserSubmitRenewalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSubmitRenewalServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserSubmitRenewalService $service;
    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserSubmitRenewalService();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
    }

    // =========================================================================
    // submit()
    // =========================================================================

    public function test_submit_creates_request_with_awaiting_payment_status(): void
    {
        $result = $this->service->submit(
            userId: $this->user->id,
            profileId: 1,
            type: 'App\Models\MemberProfile',
            plan: 'BASIC',
        );

        $this->assertInstanceOf(UserSubscriptionRequest::class, $result);
        $this->assertEquals('awaiting_payment', $result->status);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals(1, $result->profile_id);
        $this->assertEquals('BASIC', $result->plan);
    }

    public function test_submit_creates_submitted_and_awaiting_payment_logs(): void
    {
        $result = $this->service->submit(
            userId: $this->user->id,
            profileId: 1,
            type: 'App\Models\MemberProfile',
            plan: 'BASIC',
        );

        $logs = UserSubscriptionLog::where('subscription_request_id', $result->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $logs);
        $this->assertEquals('submitted', $logs[0]->action);
        $this->assertEquals('user', $logs[0]->performed_by);
        $this->assertEquals('awaiting_payment', $logs[1]->action);
        $this->assertEquals('user', $logs[1]->performed_by);
    }

    public function test_submit_updates_existing_request_for_same_user_and_type(): void
    {
        $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'BASIC');
        $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');

        $this->assertEquals(1, UserSubscriptionRequest::where('user_id', $this->user->id)->count());
        $this->assertEquals('VIP', UserSubscriptionRequest::where('user_id', $this->user->id)->first()->plan);
    }

    // =========================================================================
    // acceptPayment()
    // =========================================================================

    public function test_accept_payment_creates_new_subscription(): void
    {
        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');

        $this->service->acceptPayment($request->id, $this->admin->id);

        $this->assertDatabaseHas('user_subscriptions', [
            'subscribable_id' => 1,
            'subscribable_type' => 'App\Models\MemberProfile',
            'plan' => 'VIP',
        ]);
    }

    public function test_accept_payment_updates_request_status(): void
    {
        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');

        $result = $this->service->acceptPayment($request->id, $this->admin->id);

        $this->assertEquals('payment_accepted', $result->status);
    }

    public function test_accept_payment_logs_action_by_admin(): void
    {
        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');

        $this->service->acceptPayment($request->id, $this->admin->id);

        $this->assertDatabaseHas('user_subscription_logs', [
            'subscription_request_id' => $request->id,
            'user_id' => $this->admin->id,
            'action' => 'payment_accepted',
            'performed_by' => 'admin',
        ]);
    }

    public function test_accept_payment_soft_deletes_old_subscription(): void
    {
        // Create an existing subscription
        $oldSubscription = UserSubscription::create([
            'subscribable_id' => 1,
            'subscribable_type' => 'App\Models\MemberProfile',
            'plan' => 'BASIC',
            'start_date' => now()->subMonth(),
        ]);

        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');
        $this->service->acceptPayment($request->id, $this->admin->id);

        // Old subscription should be soft deleted
        $this->assertSoftDeleted('user_subscriptions', ['id' => $oldSubscription->id]);

        // New subscription should exist
        $activeSubscription = UserSubscription::where('subscribable_id', 1)
            ->where('subscribable_type', 'App\Models\MemberProfile')
            ->latest()
            ->first();

        $this->assertNotNull($activeSubscription);
        $this->assertEquals('VIP', $activeSubscription->plan);
    }

    // =========================================================================
    // getIncomingRequests()
    // =========================================================================

    public function test_get_incoming_requests_returns_only_awaiting_payment(): void
    {
        $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'BASIC');

        $anotherUser = User::factory()->create();
        $request2 = $this->service->submit($anotherUser->id, 2, 'App\Models\NursingProfile', 'PROFESSIONAL');
        $this->service->acceptPayment($request2->id, $this->admin->id);

        $incoming = $this->service->getIncomingRequests();

        $this->assertCount(1, $incoming);
        $this->assertEquals($this->user->id, $incoming->first()->user_id);
    }

    public function test_get_incoming_requests_loads_user_and_logs_relations(): void
    {
        $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'BASIC');

        $incoming = $this->service->getIncomingRequests();

        $this->assertTrue($incoming->first()->relationLoaded('user'));
        $this->assertTrue($incoming->first()->relationLoaded('logs'));
    }

    // =========================================================================
    // getRequestWithHistory()
    // =========================================================================

    public function test_get_request_with_history_returns_full_data(): void
    {
        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'BASIC');
        $this->service->acceptPayment($request->id, $this->admin->id);

        $history = $this->service->getRequestWithHistory($request->id);

        $this->assertArrayHasKey('request', $history);
        $this->assertArrayHasKey('allLogs', $history);
        $this->assertArrayHasKey('currentSubscription', $history);
        $this->assertArrayHasKey('pastSubscriptions', $history);

        $this->assertNotNull($history['currentSubscription']);
        $this->assertEquals('BASIC', $history['request']->plan);
    }

    public function test_get_request_with_history_shows_past_subscriptions(): void
    {
        // Create old subscription then accept a new one
        UserSubscription::create([
            'subscribable_id' => 1,
            'subscribable_type' => 'App\Models\MemberProfile',
            'plan' => 'BASIC',
            'start_date' => now()->subMonths(2),
        ]);

        $request = $this->service->submit($this->user->id, 1, 'App\Models\MemberProfile', 'VIP');
        $this->service->acceptPayment($request->id, $this->admin->id);

        $history = $this->service->getRequestWithHistory($request->id);

        $this->assertCount(1, $history['pastSubscriptions']);
        $this->assertEquals('BASIC', $history['pastSubscriptions']->first()->plan);
    }
}
