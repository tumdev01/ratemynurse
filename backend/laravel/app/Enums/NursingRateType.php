<?php
namespace App\Enums;

final class NursingRateType
{
    const SERVICE = 'คุณภาพการบริการ';
    const COMMUNICATION = 'การสื่อสารเข้าใจง่าย';
    const TIME = 'ความตรงต่อเวลา';
    const CARE = 'การดูแลเอาใจใส่';
    const APPOINTMENT = 'การนัดหมาย';

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
