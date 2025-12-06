<?php
namespace App\Enums;

final class Skill
{
    const BASIC_PHYSIOTHERAPY = 'กายภาพบำบัดเบื้องต้น';
    const EATEN = 'การทานอาหาร';
    const MEDICATION_VITALSIGNS = 'จัดยา และวัดสัญญาณชีพ';
    const BOWELCARE = 'การขับถ่าย/ชําระร่างกาย';
    const FEEDING = 'ป้อนอาหาร';
    const GLUCOSE_INSULIN = 'เจาะตรวจน้ำตาลและฉีดอินซูลิน';
    const TUBE_FEEDING = 'ให้อาหารทางสายยาง (ติดเตียง)';
    const URINARY_CATHETER = 'ใส่สายสวนปัสสาวะ';
    const SUCTION_SECRETION = 'ดูดเสมหะ (ติดเตียง)';
    const BEDRIDDEN_CARE = 'การดูแลผู้ป่วยติดเตียง';
    const MEDICATION_AND_INJECTION = 'การให้ยาและการฉีดยา';
    const DAILY_ACTIVITY_ASSISTANCE = 'การช่วยเหลือกิจกรรมประจำวัน (ADL)';
    const WOUND_CARE = 'การดูแลแผลและแต่งแผล';
    const MEDICAL_EQUIPMENT_USE = 'การใช้เครื่องทางการแพทย์';
    const CHRONIC_CARE = 'การดูแลผู้ป่วยโรคเรื้อรัง';
    const DEMENTIA_CARE = 'การดูแลผู้ป่วยโรคอัลไซเมอร์/สมองเสื่อม';
    const OTHER_SKILLS = 'ทักษะอื่นๆ';
    const MASSAGE_AND_PHYSICAL_THERAPY = 'การนวดและกายภาพบำบัด';
    
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
