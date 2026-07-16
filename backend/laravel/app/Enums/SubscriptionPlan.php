<?php

namespace App\Enums;

use App\Models\MemberProfile;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;

enum SubscriptionPlan: string
{
    case BASIC = 'BASIC';
    case VIP = 'VIP';
    case PROFESSIONAL = 'PROFESSIONAL';
    case ENTERPRISE = 'ENTERPRISE';

    public static function forType(string $type): array
    {
        return match ($type) {
            MemberProfile::class => [self::BASIC, self::VIP],
            NursingProfile::class => [self::BASIC, self::PROFESSIONAL],
            NursingHomeProfile::class => [self::BASIC, self::PROFESSIONAL, self::ENTERPRISE],
            default => [],
        };
    }

    public static function valuesForType(string $type): array
    {
        return array_map(fn ($plan) => $plan->value, self::forType($type));
    }
}
