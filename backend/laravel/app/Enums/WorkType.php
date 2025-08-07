<?php

namespace App\Enums;

enum WorkType: string
{
    case FULLTIME = 'เต็มเวลา';
    case PARTTIME = 'ไม่เต็มเวลา';
    case DEPENDS_ON = 'ตามงาน';
}
