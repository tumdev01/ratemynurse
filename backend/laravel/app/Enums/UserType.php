<?php

namespace App\Enums;

enum UserType: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case NURSING = 'NURSING';
    case NURSING_HOME = 'NURSING_HOME';
    case GUEST = 'GUEST';
    case MEMBER = 'MEMBER';
    case API = 'API';
}
