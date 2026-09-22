<?php

namespace App\Enums;

enum AccountTypesEnum: string
{
    case NORMAL = 'normal';
    case IRONMAN = 'ironman';
    case HARDCORE_IRONMAN = 'hardcore_ironman';
    case ULTIMATE_IRONMAN = 'ultimate_ironman';
    case GROUP_IRONMAN = 'group_ironman';

    /**
     * Every account type keyed by case name, in display order.
     *
     * @return array<string, string>
     */
    public static function returnAllAccountTypes(): array
    {
        return [
            self::NORMAL->name => self::NORMAL->value,
            self::IRONMAN->name => self::IRONMAN->value,
            self::HARDCORE_IRONMAN->name => self::HARDCORE_IRONMAN->value,
            self::ULTIMATE_IRONMAN->name => self::ULTIMATE_IRONMAN->value,
            self::GROUP_IRONMAN->name => self::GROUP_IRONMAN->value,
        ];
    }
}
