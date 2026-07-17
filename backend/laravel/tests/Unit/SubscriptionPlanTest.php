<?php

namespace Tests\Unit;

use App\Enums\SubscriptionPlan;
use PHPUnit\Framework\TestCase;

class SubscriptionPlanTest extends TestCase
{
    public function test_member_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\MemberProfile');

        $this->assertEquals(['BASIC', 'ENTERPRISE'], $plans);
    }

    public function test_nursing_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\NursingProfile');

        $this->assertEquals(['BASIC', 'PROFESSIONAL', 'VIP'], $plans);
    }

    public function test_nursing_home_profile_allowed_plans(): void
    {
        $plans = SubscriptionPlan::valuesForType('App\Models\NursingHomeProfile');

        $this->assertEquals(['BASIC', 'PREMIUM', 'ENTERPRISE'], $plans);
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
        $this->assertSame(SubscriptionPlan::ENTERPRISE, $plans[1]);
    }

    public function test_price_for_member_plans(): void
    {
        $this->assertEquals(0, SubscriptionPlan::priceFor('App\Models\MemberProfile', 'BASIC'));
        $this->assertEquals(199, SubscriptionPlan::priceFor('App\Models\MemberProfile', 'ENTERPRISE'));
    }

    public function test_price_for_nursing_plans(): void
    {
        $this->assertEquals(0, SubscriptionPlan::priceFor('App\Models\NursingProfile', 'BASIC'));
        $this->assertEquals(590, SubscriptionPlan::priceFor('App\Models\NursingProfile', 'PROFESSIONAL'));
        $this->assertEquals(990, SubscriptionPlan::priceFor('App\Models\NursingProfile', 'VIP'));
    }

    public function test_price_for_nursing_home_plans(): void
    {
        $this->assertEquals(0, SubscriptionPlan::priceFor('App\Models\NursingHomeProfile', 'BASIC'));
        $this->assertEquals(2990, SubscriptionPlan::priceFor('App\Models\NursingHomeProfile', 'PREMIUM'));
        $this->assertEquals(4990, SubscriptionPlan::priceFor('App\Models\NursingHomeProfile', 'ENTERPRISE'));
    }

    public function test_price_for_unknown_combination_defaults_to_zero(): void
    {
        $this->assertEquals(0, SubscriptionPlan::priceFor('App\Models\MemberProfile', 'VIP'));
    }
}
