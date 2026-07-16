<?php

namespace Tests\Unit;

use App\Enums\SubscriptionPlan;
use PHPUnit\Framework\TestCase;

class SubscriptionPlanTest extends TestCase
{
    public function test_member_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\MemberProfile');

        $this->assertEquals(['BASIC', 'VIP'], $plans);
    }

    public function test_nursing_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\NursingProfile');

        $this->assertEquals(['BASIC', 'PROFESSIONAL'], $plans);
    }

    public function test_nursing_home_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\NursingHomeProfile');

        $this->assertEquals(['BASIC', 'PROFESSIONAL', 'ENTERPRISE'], $plans);
    }

    public function test_unknown_type_returns_empty_array(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\UnknownModel');

        $this->assertEquals([], $plans);
    }

    public function test_for_type_returns_enum_instances(): void
    {
        $plans = SubscriptionPlan::forType('App\Models\MemberProfile');

        $this->assertCount(2, $plans);
        $this->assertSame(SubscriptionPlan::BASIC, $plans[0]);
        $this->assertSame(SubscriptionPlan::VIP, $plans[1]);
    }
}
