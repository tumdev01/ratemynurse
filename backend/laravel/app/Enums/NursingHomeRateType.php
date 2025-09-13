<?php
namespace App\Enums;

final class NursingHomeRateType
{
    const SERVICE = 'การบริการ';
    const COMMUNICATION = 'การสื่อสาร';
    const PUNCTUALITY = 'ตรงต่อเวลา';
    const TAKE_CARE = 'ดูแลเอาใจใส่';

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
