<?php
namespace App\Enums;

final class CenterHighlightType
{
    const RESORT = 'โรงแรมสไตล์รีสอร์ท';
    const PHYSICAL_THERAPY = 'กายภาพบำบัดภายในศูนย์';
    const SECURITY = 'รักษาความปลอดภัย';
    const SUPPORT24 = 'พยาบาล/แพทย์ประจำ 24 ชม.';
    const HEALTH_CHECK_YEARLY = 'บริการตรวจสุขภาพประจำปี';
    const GARDEN = 'สวนหย่อม/พื้นที่นันทนาการ';
    const CLOSE_TOWN = 'ศูนย์ดูแลใกล้ตัวเมือง';
    const MOUTAIN_VIEW = 'ใกล้ชิดธรรมชาติ วิวภูเขา';

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
