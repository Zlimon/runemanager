<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case PvmMass = 'pvm_mass';
    case ClanWars = 'clan_wars';
    case SkillingEvent = 'skilling_event';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::PvmMass => 'PvM mass',
            self::ClanWars => 'Clan wars',
            self::SkillingEvent => 'Skilling event',
            self::Custom => 'Custom',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
