<?php

namespace Database\Seeders;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\WomAchievement;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use App\Services\WiseOldMan\WomMetrics;
use App\Support\Experience;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Offline Wise Old Man data for every seeded account: a `wom_players` row, a
 * year of `WomDailySnapshot` documents with every metric, and the
 * achievements and personal records that history implies, so the Gains tab,
 * the Adventurer's Log, /progress and the leaderboards have something to
 * draw without touching the API.
 *
 * Each linked account's newest document is its seeded hiscores (skills,
 * bosses, clues and minigames under WOM's metric names; `exp` is the sum of
 * the skills). Walking back a day at a time undoes that day's gains, spread
 * over a few focus skills and bosses.
 *
 * The mix is deliberate: most accounts are linked and active, a few went
 * quiet more than 30 days ago (inactive), one is unknown to WOM, one is
 * hidden from /progress, and two have opted in to tracking. Run
 * `LiveWomSeeder` to replace the synthetic numbers with real ones.
 *
 * Idempotent: accounts that already have a row keep it, a series only gets
 * the days it is missing (and older documents without metric maps get them),
 * and an account that already has achievements keeps its milestones.
 */
class DemoWomSeeder extends Seeder
{
    /** Days of history per linked account, today included. */
    public const DAYS = 365;

    /** WOM's highest possible total experience (every skill at 200m). */
    private const MAX_EXP = 4_600_000_000;

    /** A boss below this kill count is unranked, so absent from a document. */
    public const MIN_BOSS_KILLS = 5;

    /** Record periods and their length in days. */
    private const RECORD_WINDOWS = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        fake()->seed(DatabaseSeeder::FAKER_SEED);

        $accounts = Account::query()->with('hiscore')->orderBy('id')->get(['id', 'username', 'account_type', 'level', 'xp']);

        if ($accounts->isEmpty()) {
            $this->command->warn('DemoWomSeeder: no accounts found; run AccountSeeder first.');

            return;
        }

        /** @var array<int, array{exp: int, skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}> $latest */
        $latest = [];

        foreach ($accounts as $account) {
            $latest[$account->id] = $this->latestFor($account);
        }

        $existing = WomPlayer::query()->pluck('account_id')->all();
        $created = 0;

        foreach ($accounts->values() as $index => $account) {
            if (in_array($account->id, $existing, true)) {
                continue;
            }

            $this->createPlayer($account, $latest[$account->id]['exp'], $this->roleFor($index, $accounts->count()));
            $created++;
        }

        try {
            [$days, $milestones] = $this->seedHistory($latest);
        } catch (Throwable $e) {
            $this->command->warn('DemoWomSeeder: could not seed the daily series — '.$e->getMessage());
            $this->command->line('  Seeding continues; start the mongodb service and re-run db:seed to fill them in.');

            return;
        }

        $this->command->info(sprintf(
            'DemoWomSeeder: created %d Wise Old Man players, %d daily points and milestones for %d accounts.',
            $created,
            $days,
            $milestones,
        ));
    }

    /**
     * The account's current metrics under WOM's names: its seeded hiscores,
     * or a synthetic set for an account that has none.
     *
     * @return array{exp: int, skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}
     */
    private function latestFor(Account $account): array
    {
        $entries = $account->hiscore?->entries
            ?? AccountHiscore::factory()->raw(['account_id' => $account->id])['entries'];

        $skills = [];

        foreach ((array) ($entries['skills'] ?? []) as $slug => $entry) {
            $xp = (int) ($entry['xp'] ?? -1);

            if ($slug !== 'overall' && $xp >= 0) {
                $skills[WomMetrics::fromLocalSlug($slug)] = $xp;
            }
        }

        $bosses = [];
        $activities = [];

        foreach ((array) ($entries['activities'] ?? []) as $slug => $entry) {
            $metric = WomMetrics::fromLocalSlug($slug);
            $score = (int) ($entry['score'] ?? -1);

            if ($score < 0) {
                continue;
            }

            if (WomMetrics::group($metric) === 'boss') {
                $bosses[$metric] = $score;
            } elseif (WomMetrics::group($metric) === 'activity') {
                $activities[$metric] = $score;
            }
        }

        return ['exp' => array_sum($skills), 'skills' => $skills, 'bosses' => $bosses, 'activities' => $activities];
    }

    /**
     * Which kind of row an account gets, by its position in id order, so the
     * same accounts get the same role on every seed.
     *
     * @return 'active'|'inactive'|'not_found'|'hidden'|'opted_in'
     */
    private function roleFor(int $index, int $count): string
    {
        return match (true) {
            $index === $count - 1 => 'not_found',
            $index < 2 => 'opted_in',
            $index === 2 => 'hidden',
            $index % 6 === 5 => 'inactive',
            default => 'active',
        };
    }

    private function createPlayer(Account $account, int $exp, string $role): void
    {
        if ($role === 'not_found') {
            WomPlayer::factory()->notFound()->create(['account_id' => $account->id]);

            return;
        }

        $type = $account->account_type instanceof AccountTypesEnum
            ? $account->account_type
            : AccountTypesEnum::tryFrom((string) $account->account_type);

        $level = (int) $account->level;
        $ehp = $exp / $this->expPerEhp($type);

        $lastChanged = $role === 'inactive'
            ? now()->subDays(fake()->numberBetween(40, 200))->subMinutes(fake()->numberBetween(0, 1440))
            : now()->subMinutes(fake()->numberBetween(10, 72 * 60));

        $factory = $role === 'inactive' ? WomPlayer::factory()->inactive() : WomPlayer::factory();

        $factory->create([
            'account_id' => $account->id,
            'wom_player_id' => 100_000 + $account->id,
            'type' => match ($type) {
                AccountTypesEnum::IRONMAN, AccountTypesEnum::GROUP_IRONMAN => 'ironman',
                AccountTypesEnum::HARDCORE_IRONMAN => 'hardcore',
                AccountTypesEnum::ULTIMATE_IRONMAN => 'ultimate',
                default => 'regular',
            },
            'build' => 'main',
            'status' => 'active',
            'exp' => $exp,
            'ehp' => round($ehp, 4),
            'ehb' => round($ehp * fake()->randomFloat(3, 0.05, 0.8), 4),
            'ttm' => $level >= 2277 ? 0 : round((2277 - max($level, 32)) * fake()->randomFloat(2, 0.6, 1.2), 4),
            'tt200m' => round(max(0, self::MAX_EXP - $exp) / 210_000, 4),
            'combat_level' => min(126, 3 + intdiv(max($level, 32), 18)),
            'registered_at' => now()->subDays(fake()->numberBetween(400, 1460)),
            'wom_updated_at' => $role === 'inactive' ? now()->subDays(fake()->numberBetween(1, 10)) : $lastChanged,
            'last_changed_at' => $lastChanged,
            'update_requested_at' => $role === 'opted_in' ? $lastChanged : null,
            'backfilled_at' => now(),
            'milestones_synced_at' => null,
            'not_found_at' => null,
            'synced_at' => now(),
            'tracking_opt_in' => $role === 'opted_in',
            'show_on_progress' => $role !== 'hidden',
        ]);
    }

    /** Ironmen earn less XP per efficient hour than mains. */
    private function expPerEhp(?AccountTypesEnum $type): float
    {
        $base = $type === null || $type === AccountTypesEnum::NORMAL ? 420_000 : 330_000;

        return $base * fake()->randomFloat(2, 0.85, 1.15);
    }

    /**
     * Write each linked account's missing days and, once, its milestones.
     * Returns [documents written, accounts given milestones].
     *
     * @param  array<int, array{exp: int, skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}>  $latest
     * @return array{int, int}
     */
    private function seedHistory(array $latest): array
    {
        $written = 0;
        $milestones = 0;

        foreach (WomPlayer::query()->linked()->orderBy('account_id')->get() as $player) {
            $current = $latest[$player->account_id] ?? null;

            if ($current === null) {
                continue;
            }

            // The row's total follows the hiscores it is drawn from.
            if ($player->exp !== $current['exp']) {
                $player->forceFill(['exp' => $current['exp']])->save();
            }

            $series = $this->seriesFor($player, $current);
            $written += $this->writeSeries($player->account_id, $series);

            if (! WomAchievement::query()->where('account_id', $player->account_id)->exists()) {
                $this->seedMilestones($player, $series);
                $milestones++;
            }
        }

        return [$written, $milestones];
    }

    /**
     * Insert the days the account lacks and rewrite days that only hold the
     * headline numbers; days that already have their maps are left alone.
     *
     * @param  list<array<string, mixed>>  $series
     */
    private function writeSeries(int $accountId, array $series): int
    {
        $complete = WomDailySnapshot::query()
            ->where('account_id', $accountId)
            ->whereNotNull('skills')
            ->pluck('date')
            ->flip();

        $rows = array_values(array_filter($series, fn (array $row): bool => ! isset($complete[$row['date']])));

        // A headline-only day is rewritten whole, so its total stays the sum
        // of its skills.
        WomDailySnapshot::query()->upsert($rows, ['account_id', 'date'], ['exp', 'ehp', 'ehb', 'skills', 'bosses', 'activities']);

        return count($rows);
    }

    /**
     * A year of daily documents, walked backwards from the current metrics so
     * the last document is exactly the hiscores. Some days are flat, the odd
     * one is a big grind, and an inactive account is flat since its last
     * change. Oldest first.
     *
     * @param  array{exp: int, skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}  $current
     * @return list<array{account_id: int, date: string, exp: int, ehp: float, ehb: float, skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}>
     */
    private function seriesFor(WomPlayer $player, array $current): array
    {
        $today = Carbon::now('UTC')->startOfDay();
        $quietSince = $player->last_changed_at?->copy()->utc()->startOfDay();

        $skills = $current['skills'];
        $bosses = $current['bosses'];
        $activities = $current['activities'];
        $exp = $current['exp'];
        $ehp = $player->ehp;
        $ehb = $player->ehb;
        $expPerEhp = $ehp > 0 && $exp > 0 ? $exp / $ehp : 400_000;
        $typicalGain = min(5_000_000, max(50_000, intdiv($exp, 400)));

        $rows = [];

        for ($offset = 0; $offset < self::DAYS; $offset++) {
            $day = $today->copy()->subDays($offset);

            $rows[] = [
                'account_id' => $player->account_id,
                'date' => $day->toDateString(),
                'exp' => array_sum($skills),
                'ehp' => round($ehp, 4),
                'ehb' => round($ehb, 4),
                'skills' => $skills,
                'bosses' => array_filter($bosses, fn (int $kills): bool => $kills >= self::MIN_BOSS_KILLS),
                'activities' => $activities,
            ];

            // The gain that produced this day's values; nothing to undo on a
            // day after the account went quiet.
            if ($quietSince !== null && $day->gt($quietSince)) {
                continue;
            }

            $roll = fake()->numberBetween(1, 100);
            $gain = match (true) {
                $roll <= 25 => 0,
                $roll <= 30 => (int) ($typicalGain * fake()->randomFloat(2, 3, 8)),
                default => (int) ($typicalGain * fake()->randomFloat(2, 0.4, 1.6)),
            };

            // Never walk below a tenth of today's total.
            if (array_sum($skills) - $gain < $exp / 10) {
                $gain = 0;
            }

            if ($gain === 0) {
                continue;
            }

            $undone = $this->undo($skills, $gain, fake()->numberBetween(1, 3), 1);
            $ehp = max(0.0, $ehp - $undone / $expPerEhp);

            if (fake()->boolean(40)) {
                $kills = $this->undo($bosses, fake()->numberBetween(1, 40), fake()->numberBetween(1, 2), 1);
                $ehb = max(0.0, $ehb - $kills * fake()->randomFloat(3, 0.01, 0.08));
            }

            // Now and then a clue: its tier and the all-tiers count both drop.
            $tiers = array_filter(
                $activities,
                fn (int $score, string $metric): bool => $score > 0 && str_starts_with($metric, 'clue_scrolls_') && $metric !== 'clue_scrolls_all',
                ARRAY_FILTER_USE_BOTH,
            );

            if ($tiers !== [] && ($activities['clue_scrolls_all'] ?? 0) > 0 && fake()->boolean(15)) {
                $activities[fake()->randomElement(array_keys($tiers))]--;
                $activities['clue_scrolls_all']--;
            }
        }

        return array_reverse($rows);
    }

    /**
     * Take up to `$amount` off `$focus` random non-zero entries of `$values`,
     * never below zero. Returns what was actually taken.
     *
     * @param  array<string, int>  $values
     */
    private function undo(array &$values, int $amount, int $focus, int $minimum): int
    {
        $candidates = array_keys(array_filter($values, fn (int $value): bool => $value >= $minimum));

        if ($candidates === []) {
            return 0;
        }

        $picked = fake()->randomElements($candidates, min($focus, count($candidates)));
        $taken = 0;

        foreach ($picked as $i => $metric) {
            $share = $i === count($picked) - 1 ? $amount - $taken : (int) ($amount * fake()->randomFloat(2, 0.2, 0.6));
            $take = min(max(0, $share), $values[$metric]);
            $values[$metric] -= $take;
            $taken += $take;
        }

        return $taken;
    }

    /**
     * Achievements and personal records as WOM would report them for this
     * history. A threshold crossed inside the year is dated on its crossing
     * day; one already reached on the first day gets an earlier date, except
     * the two oldest, which WOM cannot date (null). Plus one legacy
     * pre-Sailing base-stats row.
     *
     * @param  list<array<string, mixed>>  $series
     */
    private function seedMilestones(WomPlayer $player, array $series): void
    {
        $first = Carbon::parse($series[0]['date'], 'UTC');
        $achievements = [];

        foreach ($this->achievementDefinitions($series[count($series) - 1]) as $definition) {
            $index = null;

            foreach ($series as $i => $row) {
                if (($definition['value'])($row) >= $definition['reach']) {
                    $index = $i;

                    break;
                }
            }

            if ($index === null) {
                continue;
            }

            $achievements[] = [
                'index' => $index,
                'name' => $definition['name'],
                'metric' => $definition['metric'],
                'measure' => $definition['measure'],
                'threshold' => $definition['threshold'],
                'achieved_at' => $index === 0
                    ? $first->copy()->subDays(fake()->numberBetween(1, 700))->addMinutes(fake()->numberBetween(0, 1439))
                    : $this->timeOn($series[$index]['date']),
                'accuracy' => fake()->boolean(15)
                    ? fake()->numberBetween(2 * 86_400_000, 10 * 86_400_000)
                    : fake()->numberBetween(0, 6 * 3_600_000),
                'legacy' => false,
            ];
        }

        usort($achievements, fn (array $a, array $b): int => $a['achieved_at'] <=> $b['achieved_at']);

        foreach (array_slice(array_keys($achievements), 0, 2) as $key) {
            $achievements[$key]['achieved_at'] = null;
            $achievements[$key]['accuracy'] = null;
        }

        $achievements[] = $this->legacyAchievement($series[count($series) - 1]['skills']);

        WomAchievement::query()->upsert(
            array_map(fn (array $row): array => ['account_id' => $player->account_id] + array_diff_key($row, ['index' => true]), $achievements),
            ['account_id', 'name'],
            ['metric', 'measure', 'threshold', 'achieved_at', 'accuracy', 'legacy'],
        );

        WomRecord::query()->upsert(
            array_map(fn (array $row): array => ['account_id' => $player->account_id] + $row, $this->records($series)),
            ['account_id', 'period', 'metric'],
            ['value', 'achieved_at'],
        );

        $player->forceFill(['milestones_synced_at' => now()])->save();
    }

    /**
     * WOM's achievement thresholds that apply to this account's metrics.
     *
     * @param  array<string, mixed>  $latest
     * @return list<array{name: string, metric: string, measure: string, threshold: int, reach: int, value: callable(array<string, mixed>): int}>
     */
    private function achievementDefinitions(array $latest): array
    {
        $definitions = [];

        foreach (array_keys($latest['skills']) as $skill) {
            foreach (['99' => Experience::forLevel(99), '50m' => 50_000_000, '100m' => 100_000_000] as $prefix => $xp) {
                $definitions[] = [
                    'name' => $prefix.' '.WomMetrics::label($skill),
                    'metric' => $skill,
                    'measure' => 'experience',
                    'threshold' => $xp,
                    'reach' => $xp,
                    'value' => fn (array $row): int => $row['skills'][$skill] ?? 0,
                ];
            }
        }

        foreach ([70, 80, 90] as $level) {
            $definitions[] = [
                'name' => "Base {$level} Stats",
                'metric' => 'overall',
                'measure' => 'levels',
                'threshold' => Experience::forLevel($level) * count($latest['skills']),
                'reach' => Experience::forLevel($level),
                'value' => fn (array $row): int => $row['skills'] === [] ? 0 : min($row['skills']),
            ];
        }

        foreach (array_keys($latest['bosses']) as $boss) {
            foreach ([50, 100, 500, 1000] as $kills) {
                $definitions[] = [
                    'name' => self::shortNumber($kills).' '.WomMetrics::label($boss).' kills',
                    'metric' => $boss,
                    'measure' => 'kills',
                    'threshold' => $kills,
                    'reach' => $kills,
                    'value' => fn (array $row): int => $row['bosses'][$boss] ?? 0,
                ];
            }
        }

        foreach (array_keys($latest['activities']) as $activity) {
            if (! str_starts_with($activity, 'clue_scrolls_')) {
                continue;
            }

            foreach ([100, 500] as $score) {
                $definitions[] = [
                    'name' => $score.' '.WomMetrics::label($activity),
                    'metric' => $activity,
                    'measure' => 'score',
                    'threshold' => $score,
                    'reach' => $score,
                    'value' => fn (array $row): int => $row['activities'][$activity] ?? 0,
                ];
            }
        }

        return $definitions;
    }

    /**
     * The highest base level the account had before Sailing, as the retired
     * achievement WOM still lists (undated).
     *
     * @param  array<string, int>  $skills
     * @return array<string, mixed>
     */
    private function legacyAchievement(array $skills): array
    {
        $preSailing = array_diff_key($skills, ['sailing' => true]);
        $lowest = $preSailing === [] ? 0 : min($preSailing);
        $level = max(10, intdiv(Experience::levelFor($lowest), 10) * 10);

        return [
            'name' => "Base {$level} Stats (Pre-Sailing)",
            'metric' => 'overall',
            'measure' => 'levels',
            'threshold' => Experience::forLevel($level) * max(1, count($preSailing)),
            'achieved_at' => null,
            'accuracy' => null,
            'legacy' => true,
        ];
    }

    /**
     * The best gain per metric over a rolling day, week, month and year, dated
     * on the day the window ended.
     *
     * @param  list<array<string, mixed>>  $series
     * @return list<array{period: string, metric: string, value: float, achieved_at: Carbon}>
     */
    private function records(array $series): array
    {
        $metrics = ['overall' => fn (array $row): ?float => (float) $row['exp']];

        foreach (['skills', 'bosses', 'activities'] as $group) {
            foreach (array_keys($series[count($series) - 1][$group]) as $metric) {
                $metrics[$metric] = fn (array $row): ?float => isset($row[$group][$metric]) ? (float) $row[$group][$metric] : null;
            }
        }

        $metrics['ehp'] = fn (array $row): ?float => (float) $row['ehp'];
        $metrics['ehb'] = fn (array $row): ?float => (float) $row['ehb'];

        $records = [];

        foreach ($metrics as $metric => $valueOf) {
            $values = array_map($valueOf, $series);

            foreach (self::RECORD_WINDOWS as $period => $days) {
                $best = 0.0;
                $bestIndex = null;

                for ($i = 1; $i < count($values); $i++) {
                    $start = $values[max(0, $i - $days)];

                    if ($values[$i] !== null && $start !== null && $best < $values[$i] - $start) {
                        $best = $values[$i] - $start;
                        $bestIndex = $i;
                    }
                }

                if ($bestIndex !== null) {
                    $records[] = [
                        'period' => $period,
                        'metric' => $metric,
                        'value' => round($best, 4),
                        'achieved_at' => $this->timeOn($series[$bestIndex]['date']),
                    ];
                }
            }
        }

        return $records;
    }

    /** A random moment on a UTC day, never in the future. */
    private function timeOn(string $date): Carbon
    {
        $at = Carbon::parse($date, 'UTC')->addMinutes(fake()->numberBetween(0, 1439));

        return $at->isFuture() ? now()->utc()->subMinutes(fake()->numberBetween(1, 60)) : $at;
    }

    /** WOM's short threshold style: 50, 500, 1k. */
    private static function shortNumber(int $number): string
    {
        return $number >= 1000 ? ($number / 1000).'k' : (string) $number;
    }
}
