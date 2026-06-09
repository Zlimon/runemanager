<?php

namespace App\Enums;

enum AccountTypesEnum: string
{
    case NORMAL = 'normal';
    case IRONMAN = 'ironman';
    case HARDCORE_IRONMAN = 'hardcore_ironman';
    case ULTIMATE_IRONMAN = 'ultimate_ironman';
    case GROUP_IRONMAN = 'group_ironman';

    public static function returnAllAccountTypes($flip = false): array
    {
        $allAccountTypes = [
            AccountTypesEnum::NORMAL->name => AccountTypesEnum::NORMAL->value,
            AccountTypesEnum::IRONMAN->name => AccountTypesEnum::IRONMAN->value,
            AccountTypesEnum::HARDCORE_IRONMAN->name => AccountTypesEnum::HARDCORE_IRONMAN->value,
            AccountTypesEnum::ULTIMATE_IRONMAN->name => AccountTypesEnum::ULTIMATE_IRONMAN->value,
            AccountTypesEnum::GROUP_IRONMAN->name => AccountTypesEnum::GROUP_IRONMAN->value,
        ];

        return $flip ? array_flip($allAccountTypes) : $allAccountTypes;
    }
}
