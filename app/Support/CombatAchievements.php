<?php

namespace App\Support;

/**
 * SPEC §5.2/§7.1 — Combat Achievements metadata. We track total points and the
 * count of completed tasks per tier (read by the plugin from CA_POINTS +
 * CA_TOTAL_TASKS_COMPLETED_*), mirroring the in-game overview's "27/41" panel.
 * Live task unlocks reach the feed via a separate push, not this snapshot.
 */
class CombatAchievements
{
    /**
     * Total tasks per tier, in ascending order. These are the same for every
     * player and only change when Jagex adds a CA batch (a few times a year);
     * the completed counts always come live from varbits, so only this
     * denominator can lag — update it when the totals change. (Sum = 637.)
     *
     * @var array<string, int>
     */
    public const TIER_TOTALS = [
        'easy' => 41,
        'medium' => 60,
        'hard' => 85,
        'elite' => 162,
        'master' => 168,
        'grandmaster' => 121,
    ];

    /** @return list<string> */
    public static function tiers(): array
    {
        return array_keys(self::TIER_TOTALS);
    }

    /**
     * Normalise a pushed tier map into a clean {tier: completedCount} map over
     * the canonical tier set, clamping to non-negative ints and defaulting
     * missing tiers to 0.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, int>
     */
    public static function normaliseTiers(array $input): array
    {
        $out = [];
        foreach (self::tiers() as $tier) {
            $out[$tier] = max(0, (int) ($input[$tier] ?? 0));
        }

        return $out;
    }

    /**
     * Total tasks completed across all tiers.
     *
     * @param  array<string, int>  $tiers
     */
    public static function completedTaskCount(array $tiers): int
    {
        return array_sum(self::normaliseTiers($tiers));
    }
}
