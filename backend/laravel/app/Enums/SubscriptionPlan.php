<?php

namespace App\Enums;

use App\Models\MemberProfile;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;

enum SubscriptionPlan: string
{
    case BASIC = 'BASIC';
    case PROFESSIONAL = 'PROFESSIONAL';
    case VIP = 'VIP';
    case PREMIUM = 'PREMIUM';
    case ENTERPRISE = 'ENTERPRISE';

    public static function forType(string $type): array
    {
        return match ($type) {
            MemberProfile::class => [self::BASIC, self::ENTERPRISE],
            NursingProfile::class => [self::BASIC, self::PROFESSIONAL, self::VIP],
            NursingHomeProfile::class => [self::BASIC, self::PREMIUM, self::ENTERPRISE],
            default => [],
        };
    }

    public static function valuesForType(string $type): array
    {
        return array_map(fn ($plan) => $plan->value, self::forType($type));
    }

    protected static function prices(): array
    {
        return [
            MemberProfile::class => [
                self::BASIC->value => 0,
                self::ENTERPRISE->value => 199,
            ],
            NursingProfile::class => [
                self::BASIC->value => 0,
                self::PROFESSIONAL->value => 590,
                self::VIP->value => 990,
            ],
            NursingHomeProfile::class => [
                self::BASIC->value => 0,
                self::PREMIUM->value => 2990,
                self::ENTERPRISE->value => 4990,
            ],
        ];
    }

    public static function priceFor(string $type, string $plan): int
    {
        return self::prices()[$type][$plan] ?? 0;
    }
}
