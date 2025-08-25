<?php

namespace App\Enums;

final class AdditionalServiceType
{
    const FOOD = 'บริการอาหาร';
    const TRANSPORTATION = 'บริการรับส่ง';
    const LAUNDRY = 'ซักรีด';
    const RECREATIONAL_ACTIVITIES = 'กิจกรรมนันทนาการ';
    const SOCIAL_WORK_ACTIVITIES = 'กิจกรรมสังคมสงเคราะห์';
    const SPIRITUAL_ACTIVITIES = 'กิจกรรมทางจิตวิญญาณ';

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
