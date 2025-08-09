<?php

namespace App\Enums;

final class SpecialFacilityType
{
    // ห้องพิเศษและสิ่งอำนวยความสะดวก
    const NURSE_STATION = 'ห้องพยาบาล/สถานีพยาบาล';
    const EMERGENCY_ROOM = 'ห้องฉุกเฉิน';
    const EXAMINATION_ROOM = 'ห้องตรวจ';
    const MEDICINE_ROOM = 'ห้องยา';
    const KITCHEN_CAFETERIA = 'ห้องครัว/โรงอาหาร';
    const DINING_ROOM = 'ห้องรับประทานอาหาร';
    const ACTIVITY_ROOM = 'ห้องกิจกรรม';
    const PHYSICAL_THERAPY_ROOM = 'ห้องกายภาพบำบัด';
    const MEETING_ROOM = 'ห้องประชุม';
    const OFFICE_ROOM = 'ห้องออฟฟิศ';
    const LAUNDRY_ROOM = 'ห้องซักรีด';

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
