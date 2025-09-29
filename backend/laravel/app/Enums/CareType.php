<?php
namespace App\Enums;

final class CareType
{
    const RN = 'พยาบาลวิชาชีพ (RN)';
    const PN = 'ผู้ช่วยพยาบาล (PN)';
    const NA = 'พนักงานผู้ช่วยการพยาบาล (NA)';
    const CG = 'คนดูแล';
    const MAID= 'แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)';
    const ETC = 'อื่นๆ';

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
