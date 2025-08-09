<?php
namespace App\Enums;

final class ServiceType extends Enum
{
    const HOME_NURSE_CARE = 'พยาบาลดูแลตามบ้าน';
    const ELDERLY_CARE_CENTER = 'ศูนย์ดูแลผู้สูงอายุ';
    const MASSAGE_AND_PHYSIOTHERAPY = 'บริการนวดและกายภาพบำบัด';
    const FOOD_DELIVERY_FOR_ELDERLY = 'บริการส่งอาหารสำหรับผู้สูงอายุ';
    const TRANSPORT_TO_DOCTOR = 'บริการรับ-ส่งไปหาหมอ';
    const CHILD_CARE = 'บริการดูแลเด็ก';
    const HOME_CLEANING = 'บริการทำความสะอาดบ้าน';

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
