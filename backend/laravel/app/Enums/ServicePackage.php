<?php
namespace App\Enums;

final class ServicePackage extends Enum
{
    const DAILY = 'รายวัน';
    const WEEKLY= 'รายสัปดาห์';
    const MONTHLY = 'รายเดือน';

    public static function list(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    public static function keys(): array
    {
        return array_keys(self::list());
    }

    public static function values(): array
    {
        return array_values(self::list());
    }
}
