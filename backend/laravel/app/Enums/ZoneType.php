<?php

namespace App\Enums;

final class ZoneType
{
    const NORTH = 'ภาคเหนือ';
    const WEST = 'ภาคตะวันตก';
    const EAST = 'ภาคตะวันออก';
    const SOUNT = 'ภาคใต้';
    const NORTHEAST = 'ภาคตะวันออกเฉียงเหนือ';

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
