<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountHiscore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Synthetic OSRS hiscore entries so leaderboards / skill tabs / account cards
 * have data offline (without hitting the live hiscores API). Mirrors the shape
 * HiscoresSync produces: {skills: {slug: {rank, level, xp}, overall: {...}},
 * activities: {slug: {rank, score}}}.
 *
 * @extends Factory<AccountHiscore>
 */
class AccountHiscoreFactory extends Factory
{
    /** The 24 OSRS skill slugs, in hiscores order. */
    private const SKILLS = [
        'attack', 'defence', 'strength', 'hitpoints', 'ranged', 'prayer', 'magic',
        'cooking', 'woodcutting', 'fletching', 'fishing', 'firemaking', 'crafting',
        'smithing', 'mining', 'herblore', 'agility', 'thieving', 'slayer', 'farming',
        'runecraft', 'hunter', 'construction', 'sailing',
    ];

    /** A representative slice of boss/activity slugs (Str::slug of the hiscore names). */
    private const BOSSES = [
        'zulrah', 'vorkath', 'cerberus', 'kraken', 'giant_mole', 'kalphite_queen',
        'general_graardor', 'kril_tsutsaroth', 'commander_zilyana', 'kreearra',
        'chambers_of_xeric', 'theatre_of_blood', 'tombs_of_amascut', 'the_gauntlet',
        'alchemical_hydra', 'phantom_muspah', 'duke_sucellus', 'the_leviathan',
    ];

    private const CLUE_TIERS = ['beginner', 'easy', 'medium', 'hard', 'elite', 'master', 'all'];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $skills = [];
        $totalLevel = 0;
        $totalXp = 0;

        foreach (self::SKILLS as $slug) {
            $level = $this->faker->numberBetween(40, 99);
            $xp = $this->xpForLevel($level) + $this->faker->numberBetween(0, 50_000);
            $skills[$slug] = [
                'rank' => $this->faker->numberBetween(1, 1_500_000),
                'level' => $level,
                'xp' => $xp,
            ];
            $totalLevel += $level;
            $totalXp += $xp;
        }

        $skills['overall'] = [
            'rank' => $this->faker->numberBetween(1, 1_000_000),
            'level' => $totalLevel,
            'xp' => $totalXp,
        ];

        $activities = [];
        foreach ($this->faker->randomElements(self::BOSSES, $this->faker->numberBetween(6, count(self::BOSSES))) as $boss) {
            $activities[$boss] = [
                'rank' => $this->faker->numberBetween(1, 500_000),
                'score' => $this->faker->numberBetween(1, 3_000),
            ];
        }
        foreach (self::CLUE_TIERS as $tier) {
            $activities['clue_scrolls_'.$tier] = [
                'rank' => $this->faker->numberBetween(1, 800_000),
                'score' => $this->faker->numberBetween(0, 500),
            ];
        }

        return [
            'account_id' => Account::factory(),
            'entries' => ['skills' => $skills, 'activities' => $activities],
            'fetched_at' => now()->subMinutes($this->faker->numberBetween(0, 600)),
        ];
    }

    /** Rough OSRS XP for a given level (close enough for seed data). */
    private function xpForLevel(int $level): int
    {
        $xp = 0;
        for ($l = 1; $l < $level; $l++) {
            $xp += (int) floor($l + 300 * (2 ** ($l / 7)));
        }

        return (int) floor($xp / 4);
    }
}
