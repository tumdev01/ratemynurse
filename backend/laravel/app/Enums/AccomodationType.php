<?php

namespace App\Enums;

final class AccomodationType
{
    const SINGLE = 'อาคารชั้นเดียว';
    const RESORT = 'ที่พักสไตล์รีสอร์ต';
    const MULTI = 'อาคารหลายชั้น';

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
