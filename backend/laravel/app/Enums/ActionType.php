<?php

namespace App\Enums;

enum ActionType: string
{
    case PROFILE_VIEW = 'profile_view';
    case CLICK_CALL = 'click_call';
    case CLICK_CONTACT = 'click_contact';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
