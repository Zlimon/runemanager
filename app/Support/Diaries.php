<?php

namespace App\Support;

/**
 * SPEC §5.2 — the canonical Achievement Diary areas + tiers. 12 areas × 4 tiers
 * = 48 completable diary tiers. The plugin reports completion per (area, tier);
 * the backend normalises and stores it against this fixed set.
 */
class Diaries
{
    /** @var list<string> */
    public const TIERS = ['Easy', 'Medium', 'Hard', 'Elite'];

    /** @var list<string> */
    public const AREAS = [
        'Ardougne',
        'Desert',
        'Falador',
        'Fremennik',
        'Kandarin',
        'Karamja',
        'Kourend',
        'Lumbridge',
        'Morytania',
        'Varrock',
        'Western',
        'Wilderness',
    ];

    /**
     * Normalise an arbitrary pushed payload into a clean {area: {tier: bool}}
     * map over the canonical area/tier set, ignoring anything unexpected.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, array<string, bool>>
     */
    public static function normalise(array $input): array
    {
        $out = [];
        foreach (self::AREAS as $area) {
            $out[$area] = [];
            foreach (self::TIERS as $tier) {
                $out[$area][$tier] = (bool) ($input[$area][$tier] ?? false);
            }
        }

        return $out;
    }

    /**
     * @param  array<string, array<string, bool>>  $diaries
     */
    public static function countCompleted(array $diaries): int
    {
        $count = 0;
        foreach ($diaries as $tiers) {
            foreach ($tiers as $done) {
                if ($done) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Completed count per tier (Easy/Medium/Hard/Elite), each out of 12.
     *
     * @param  array<string, array<string, bool>>  $diaries
     * @return array<string, int>
     */
    public static function countByTier(array $diaries): array
    {
        $counts = array_fill_keys(self::TIERS, 0);
        foreach ($diaries as $tiers) {
            foreach (self::TIERS as $tier) {
                if ($tiers[$tier] ?? false) {
                    $counts[$tier]++;
                }
            }
        }

        return $counts;
    }
}
