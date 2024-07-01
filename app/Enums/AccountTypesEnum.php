<?php

namespace App\Enums;

enum AccountTypesEnum: string {
    case NORMAL  = 'normal';
    case IRONMAN = 'ironman';
    case HARDCORE_IRONMAN = 'hardcore';
    case ULTIMATE_IRONMAN = 'ultimate';

    public function returnAllAccountTypes(): array {
        return [
            AccountTypesEnum::NORMAL,
            AccountTypesEnum::IRONMAN,
            AccountTypesEnum::HARDCORE_IRONMAN,
            AccountTypesEnum::ULTIMATE_IRONMAN,
        ];
    }
}
