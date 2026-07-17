<?php

namespace Tests\Feature;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSubscriptionRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // =========================================================================
    // Submit Subscription Request - Validation
    // =========================================================================

    public function test_unauthenticated_user_cannot_submit_subscription_request(): void
    {
        $response = $this->postJson('/api/subscription/submit', [
            'profile_id' => 1,
            'type' => 'App\Models\MemberProfile',
            'plan' => 'BASIC',
        ]);

        $response->assertStatus(401);
    }

    public function test_submit_requires_profile_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'type' => 'App\Models\MemberProfile',
                'plan' => 'BASIC',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_id']);
    }

    public function test_submit_requires_type(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'plan' => 'BASIC',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_submit_requires_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    public function test_submit_rejects_invalid_type(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\InvalidModel',
                'plan' => 'BASIC',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    // =========================================================================
    // Submit - Plan Validation per Profile Type
    // =========================================================================

    public function test_member_profile_cannot_use_professional_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'PROFESSIONAL',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    public function test_member_profile_cannot_use_vip_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'VIP',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    public function test_nursing_profile_cannot_use_premium_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\NursingProfile',
                'plan' => 'PREMIUM',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    public function test_nursing_profile_cannot_use_enterprise_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\NursingProfile',
                'plan' => 'ENTERPRISE',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    public function test_nursing_home_profile_cannot_use_vip_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\NursingHomeProfile',
                'plan' => 'VIP',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan']);
    }

    // =========================================================================
    // Submit - Successful Requests
    // =========================================================================

    public function test_member_can_submit_basic_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'BASIC',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscription request submitted.',
            ]);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'profile_id' => 1,
            'type' => 'App\Models\MemberProfile',
            'plan' => 'BASIC',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_member_can_submit_enterprise_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'ENTERPRISE',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'plan' => 'ENTERPRISE',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_nursing_can_submit_professional_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 2,
                'type' => 'App\Models\NursingProfile',
                'plan' => 'PROFESSIONAL',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'type' => 'App\Models\NursingProfile',
            'plan' => 'PROFESSIONAL',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_nursing_can_submit_vip_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 2,
                'type' => 'App\Models\NursingProfile',
                'plan' => 'VIP',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'type' => 'App\Models\NursingProfile',
            'plan' => 'VIP',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_nursing_home_can_submit_enterprise_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 3,
                'type' => 'App\Models\NursingHomeProfile',
                'plan' => 'ENTERPRISE',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'type' => 'App\Models\NursingHomeProfile',
            'plan' => 'ENTERPRISE',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_nursing_home_can_submit_premium_plan(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 3,
                'type' => 'App\Models\NursingHomeProfile',
                'plan' => 'PREMIUM',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'type' => 'App\Models\NursingHomeProfile',
            'plan' => 'PREMIUM',
            'status' => 'awaiting_payment',
        ]);
    }

    // =========================================================================
    // Submit - Creates Audit Logs
    // =========================================================================

    public function test_submit_creates_two_audit_logs(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'BASIC',
            ]);

        $request = UserSubscriptionRequest::where('user_id', $this->user->id)->first();

        $this->assertDatabaseHas('user_subscription_logs', [
            'subscription_request_id' => $request->id,
            'user_id' => $this->user->id,
            'action' => 'submitted',
            'performed_by' => 'user',
        ]);

        $this->assertDatabaseHas('user_subscription_logs', [
            'subscription_request_id' => $request->id,
            'user_id' => $this->user->id,
            'action' => 'awaiting_payment',
            'performed_by' => 'user',
        ]);

        $this->assertEquals(2, UserSubscriptionLog::where('subscription_request_id', $request->id)->count());
    }

    // =========================================================================
    // Submit - UpdateOrCreate Behavior (same user, same type = update)
    // =========================================================================

    public function test_resubmit_updates_existing_request_instead_of_creating_new(): void
    {
        // First submission
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'BASIC',
            ]);

        // Second submission - same user & type, different plan
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/subscription/submit', [
                'profile_id' => 1,
                'type' => 'App\Models\MemberProfile',
                'plan' => 'ENTERPRISE',
            ]);

        // Should still be only 1 request row
        $this->assertEquals(1, UserSubscriptionRequest::where('user_id', $this->user->id)->count());

        // Plan should be updated
        $this->assertDatabaseHas('user_subscription_requests', [
            'user_id' => $this->user->id,
            'type' => 'App\Models\MemberProfile',
            'plan' => 'ENTERPRISE',
        ]);

        // Should have 4 logs total (2 per submission)
        $request = UserSubscriptionRequest::where('user_id', $this->user->id)->first();
        $this->assertEquals(4, UserSubscriptionLog::where('subscription_request_id', $request->id)->count());
    }
}
