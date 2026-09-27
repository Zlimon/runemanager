<?php

namespace Database\Seeders;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Offline Wise Old Man data for every seeded account: a `wom_players` row and
 * a year of `WomDailySnapshot` points, so the progress charts, the /progress
 * page and the leaderboards have something to draw without touching the API.
 *
 * The mix is deliberate: most accounts are linked and active, a few went
 * quiet more than 30 days ago (inactive), one is unknown to WOM, one is
 * hidden from /progress, and two have opted in to tracking. Run
 * `LiveWomSeeder` to replace the synthetic numbers with real ones.
 *
 * Idempotent: accounts that already have a row keep it, and a series only
 * gets the days it is missing.
 */
class DemoWomSeeder extends Seeder
{
    /** Days of history per linked account, today included. */
    public const DAYS = 365;

    /** WOM's highest possible total experience (every skill at 200m). */
    private const MAX_EXP = 4_600_000_000;

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        fake()->seed(DatabaseSeeder::FAKER_SEED);

        $accounts = Account::query()->orderBy('id')->get(['id', 'username', 'account_type', 'level', 'xp']);

        if ($accounts->isEmpty()) {
            $this->command->warn('DemoWomSeeder: no accounts found; run AccountSeeder first.');

            return;
        }

        $existing = WomPlayer::query()->pluck('account_id')->all();
        $created = 0;

        foreach ($accounts->values() as $index => $account) {
            if (in_array($account->id, $existing, true)) {
                continue;
            }

            $this->createPlayer($account, $this->roleFor($index, $accounts->count()));
            $created++;
        }

        try {
            $days = $this->seedSeries();
        } catch (Throwable $e) {
            $this->command->warn('DemoWomSeeder: could not seed the daily series — '.$e->getMessage());
            $this->command->line('  Seeding continues; start the mongodb service and re-run db:seed to fill them in.');

            return;
        }

        $this->command->info(sprintf('DemoWomSeeder: created %d Wise Old Man players and %d daily points.', $created, $days));
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

    private function createPlayer(Account $account, string $role): void
    {
        if ($role === 'not_found') {
            WomPlayer::factory()->notFound()->create(['account_id' => $account->id]);

            return;
        }

        $type = $account->account_type instanceof AccountTypesEnum
            ? $account->account_type
            : AccountTypesEnum::tryFrom((string) $account->account_type);

        $exp = (int) $account->xp > 0 ? (int) $account->xp : fake()->numberBetween(5_000_000, 80_000_000);
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
     * Insert each linked account's missing days. Returns the number inserted.
     */
    private function seedSeries(): int
    {
        $inserted = 0;

        foreach (WomPlayer::query()->linked()->orderBy('account_id')->get() as $player) {
            $have = WomDailySnapshot::query()
                ->where('account_id', $player->account_id)
                ->pluck('date')
                ->all();

            if (count($have) >= self::DAYS) {
                continue;
            }

            $have = array_flip($have);
            $rows = array_values(array_filter(
                $this->seriesFor($player),
                fn (array $row): bool => ! isset($have[$row['date']]),
            ));

            if ($rows !== []) {
                WomDailySnapshot::query()->insert($rows);
                $inserted += count($rows);
            }
        }

        return $inserted;
    }

    /**
     * A year of daily points, walked backwards from the row's current values
     * so the last point is exactly what the row says. Some days are flat,
     * the odd one is a big grind, and an inactive account is flat since its
     * last change.
     *
     * @return list<array{account_id: int, date: string, exp: int, ehp: float, ehb: float}>
     */
    private function seriesFor(WomPlayer $player): array
    {
        $today = Carbon::now('UTC')->startOfDay();
        $quietSince = $player->last_changed_at?->copy()->utc()->startOfDay();

        $exp = $player->exp;
        $ehp = $player->ehp;
        $ehb = $player->ehb;
        $expPerEhp = $ehp > 0 ? $exp / $ehp : 400_000;
        $typicalGain = min(5_000_000, max(50_000, intdiv($exp, 400)));

        $rows = [];

        for ($offset = 0; $offset < self::DAYS; $offset++) {
            $day = $today->copy()->subDays($offset);

            $rows[] = [
                'account_id' => $player->account_id,
                'date' => $day->toDateString(),
                'exp' => $exp,
                'ehp' => round($ehp, 4),
                'ehb' => round($ehb, 4),
            ];

            // The gain that produced this day's value; nothing to undo on a
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
            if ($exp - $gain < $player->exp / 10) {
                $gain = 0;
            }

            $exp -= $gain;
            $ehp = max(0.0, $ehp - $gain / $expPerEhp);

            if ($gain > 0 && fake()->boolean(40)) {
                $ehb = max(0.0, $ehb - fake()->randomFloat(2, 0.1, 3));
            }
        }

        return array_reverse($rows);
    }
}
