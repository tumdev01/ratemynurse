<?php
namespace App\Enums;

final class NursingHomeRateType
{
    const SERVICE = 'คุณภาพและบริการ';
    const ENVIRONMENT = 'สิ่งแวดล้อมและความสะอาด';
    const SECURE = 'ความปลอดภัยและมาตรการดูแล';
    const VALUE = 'ความคุ้มค่า';
    const INFORMATION = 'การให้ข้อมูล';

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
