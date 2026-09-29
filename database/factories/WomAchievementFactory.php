<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\WomAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A dated Wise Old Man achievement as `SyncWomMilestones` stores it. Defaults
 * to a boss kill count (a random threshold keeps names unique per account);
 * use the states for 99s, undated and legacy rows.
 *
 * @extends Factory<WomAchievement>
 */
class WomAchievementFactory extends Factory
{
    /** WOM's experience threshold for level 99. */
    public const LEVEL_99_EXPERIENCE = 13_034_431;

    private const BOSSES = ['zulrah', 'vorkath', 'the_corrupted_gauntlet', 'general_graardor', 'kree_arra'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $boss = fake()->randomElement(self::BOSSES);
        $threshold = fake()->numberBetween(1, 100_000);

        return [
            'account_id' => Account::factory(),
            'name' => self::killsName($boss, $threshold),
            'metric' => $boss,
            'measure' => 'kills',
            'threshold' => $threshold,
            'achieved_at' => now()->subDays(fake()->numberBetween(1, 365)),
            'accuracy' => fake()->numberBetween(0, 3_600_000),
            'legacy' => false,
        ];
    }

    /** 99 in the given skill (a WOM metric name). */
    public function level99(string $skill): static
    {
        return $this->state(fn (): array => [
            'name' => '99 '.ucfirst($skill),
            'metric' => $skill,
            'measure' => 'experience',
            'threshold' => self::LEVEL_99_EXPERIENCE,
        ]);
    }

    /** A kill-count threshold on a boss. */
    public function kills(?string $boss = null, int $threshold = 100): static
    {
        return $this->state(function () use ($boss, $threshold): array {
            $boss ??= fake()->randomElement(self::BOSSES);

            return [
                'name' => self::killsName($boss, $threshold),
                'metric' => $boss,
                'measure' => 'kills',
                'threshold' => $threshold,
            ];
        });
    }

    /** WOM does not know when this was achieved (it reports 1970). */
    public function undated(): static
    {
        return $this->state(fn (): array => ['achieved_at' => null, 'accuracy' => null]);
    }

    /** An achievement WOM has retired. */
    public function legacy(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Base 90 Stats (Pre-Sailing)',
            'metric' => 'overall',
            'measure' => 'levels',
            'threshold' => 90,
            'legacy' => true,
        ]);
    }

    /** WOM only knows the date to within a few days. */
    public function approximate(): static
    {
        return $this->state(fn (): array => ['accuracy' => 5 * 86_400_000]);
    }

    private static function killsName(string $boss, int $threshold): string
    {
        return number_format($threshold).' '.ucwords(str_replace('_', ' ', $boss)).' kills';
    }
}
