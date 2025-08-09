<?php
namespace App\Enums;

final class FacilityType
{
    const ELEVATOR = 'ลิฟต์';
    const WHEELCHAIR_RAMP = 'ทางลาดสำหรับรถเข็น';
    const BATHROOM_GRAB_BAR = 'ราวจับในห้องน้ำ';
    const EMERGENCY_BELL = 'กระดิ่งฉุกเฉิน';
    const CAMERA = 'กล้องวงจรปิด';
    const FIRE_SYSTEM = 'ระบบดับเพลิง';
    const BACKUP_GENERATOR = 'เครื่องปั่นไฟสำรอง';
    const AIR_CONDITIONER = 'เครื่องปรับอากาศ';
    const GARDEN_AREA = 'สวนหย่อม/พื้นที่นันทนาการ';
    const PARKING = 'ที่จอดรถ';
    const WIFI_INTERNET = 'WiFi / อินเทอร์เน็ต';
    const CENTRAL_TELEVISION = 'โทรทัศน์ส่วนกลาง';

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
