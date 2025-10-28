<?php
namespace App\Enums;

final class HomeServiceType
{
    const DAYCARE = 'การดูแลประจำวัน (Day Care)';
    const RESIDENTIAL_CARE = 'การดูแลแบบพักอาศัย';
    const RESPITE_CARE = 'การดูแลระยะสั้น';
    const SPECIAL_CARE = 'การดูแลผู้ป่วยพิเศษ';
    const REHABILITATION = 'การบำบัดฟื้นฟู';
    const REHABILITATION_PALLIATIVE_CARE = 'การบำบัดฟื้นฟู (การดูแลประคับประคอง)';
    const DEMENTIA_PATIENTS = 'การดูแลผู้ป่วยสมองเสื่อม';
    const EMERGENCY_SERVICE = 'บริการฉุกเฉิน 24 ชั่วโมง';
    const OTHER_SERVICES = 'บริการอื่นๆ';

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