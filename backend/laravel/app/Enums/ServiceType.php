<?php
namespace App\Enums;

final class ServiceType extends Enum
{
    const HomeNurseCare = 'พยาบาลดูแลตามบ้าน';
    const ElderlyCareCenter = 'ศูนย์ดูแลผู้สูงอายุ';
    const MassageAndPhysiotherapy = 'บริการนวดและกายภาพบำบัด';
    const FoodDeliveryForElderly = 'บริการส่งอาหารสำหรับผู้สูงอายุ';
    const TransportToDoctor = 'บริการรับ-ส่งไปหาหมอ';
    const ChildCare = 'บริการดูแลเด็ก';
    const HomeCleaning = 'บริการทำความสะอาดบ้าน';
}
