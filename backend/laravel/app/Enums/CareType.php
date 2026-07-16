<?php

namespace App\Enums;

enum CareType: string
{
    case RN = 'พยาบาลวิชาชีพ (RN)';
    case PN = 'ผู้ช่วยพยาบาล (PN)';
    case NA = 'พนักงานผู้ช่วยการพยาบาล (NA)';
    case CG = 'คนดูแล';
    case MAID = 'แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)';
}
