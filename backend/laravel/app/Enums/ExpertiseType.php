<?php
namespace App\Enums;

final class ExpertiseType
{
    const BEDRIDDEN_PATIENTS = 'การดูแลผู้ป่วยติดเตียง';
    const MEDICATION_INJECTIONS = 'การให้ยาและการฉีดยา';
    const ADL = 'การช่วยเหลือกิจกรรมประจำวัน (ADL)';
    const MEDICAL_EQUIPMENT = 'การใช้เครื่องทางการแพทย์';
    const PATIENTS_WITH_CHRONIC_DISEASES= 'การดูแลผู้ป่วยโรคเรื้อรัง';
    const ALZHEIMER_DEMENTIA= 'การดูแลผู้ป่วยโรคอัลไซเมอร์/สมองเสื่อม';
    const MASSAGE_PHYICAL_THERAPY = 'การนวดและกายภาพบำบัด';

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
