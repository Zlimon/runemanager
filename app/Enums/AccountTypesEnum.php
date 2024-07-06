<?php

namespace App\Enums;

enum AccountTypesEnum: string {
    case NORMAL  = 'normal';
    case IRONMAN = 'ironman';
    case HARDCORE_IRONMAN = 'hardcore';
    case ULTIMATE_IRONMAN = 'ultimate';

    public static function returnAllAccountTypes(): array {
        return [
            AccountTypesEnum::NORMAL->name => AccountTypesEnum::NORMAL->value,
            AccountTypesEnum::IRONMAN->name => AccountTypesEnum::IRONMAN->value,
            AccountTypesEnum::HARDCORE_IRONMAN->name => AccountTypesEnum::HARDCORE_IRONMAN->value,
            AccountTypesEnum::ULTIMATE_IRONMAN->name => AccountTypesEnum::ULTIMATE_IRONMAN->value,
        ];
    }
}
