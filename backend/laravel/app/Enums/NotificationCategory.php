<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case CONTACT = 'contact';
    case REVIEW = 'review';
    case UPGRADE_INVITE = 'upgrade_invite';
    case RENEWAL = 'renewal';

    public static function forUserType(string $userType): array
    {
        return match ($userType) {
            'MEMBER' => [self::CONTACT, self::UPGRADE_INVITE, self::RENEWAL],
            'NURSING', 'NURSING_HOME' => [self::CONTACT, self::REVIEW, self::UPGRADE_INVITE, self::RENEWAL],
            default => [],
        };
    }

    public static function valuesForUserType(string $userType): array
    {
        return array_map(fn ($category) => $category->value, self::forUserType($userType));
    }

    public function label(): string
    {
        return match ($this) {
            self::CONTACT => 'การติดต่อ',
            self::REVIEW => 'การรีวิว',
            self::UPGRADE_INVITE => 'เชิญชวนอัปเกรดแพ็กเกจ',
            self::RENEWAL => 'ต่ออายุแพ็คเกจ',
        };
    }
}
