<?php
namespace App\Enums;

final class HireRule
{
    const FULL_STAY = 'อยู่ประจำ ค้างคืน';
    const FULL_ROUND = 'อยู่ประจำ ไปกลับ';
    const PART_STAY = 'ชั่วคราว ค้างคืน';
    const PART_ROUND = 'ชั่วคราว ไปกลับ';
    
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
