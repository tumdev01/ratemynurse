<?php
namespace App\Enums;

final class Skill extends Enum
{
    const BASIC_PHYSIOTHERAPY = 'กายภาพบำบัดเบื้องต้น';
    const การทานอาหาร = 'การทานอาหาร';
    const MEDICATION_VITALSIGNS = 'จัดยา และวัดสัญญาณชีพ';
    const BOWELCARE = 'การขับถ่าย/ชําระร่างกาย';
    const FEEDING = 'ป้อนอาหาร';
    const GLUCOSE_INSULIN = 'เจาะตรวจน้ำตาลและฉีดอินซูลิน';
    const TUBE_FEEDING = 'ให้อาหารทางสายยาง (ติดเตียง)';
    const URINARY_CATHETER = 'ใส่สายสวนปัสสาวะ';
    const SUCTION_SECRETION = 'ดูดเสมหะ (ติดเตียง)';
    
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
