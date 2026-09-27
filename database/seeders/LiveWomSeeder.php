<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\BackfillWomSeries;
use App\Services\WiseOldMan\SyncWomPlayer;
use App\Services\WiseOldMan\WiseOldManClient;
use App\Services\WiseOldMan\WiseOldManRateLimited;
use Illuminate\Database\Seeder;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Opt-in: replaces the synthetic Wise Old Man data of every seeded account
 * with the real profile and a year of real history.
 *
 *   ./vendor/bin/sail artisan db:seed --class=LiveWomSeeder
 *
 * Read-only against WOM: it only ever GETs, whatever a row's
 * `tracking_opt_in` says, so seeding can never create a WOM profile. An
 * account WOM does not know keeps its synthetic data and is reported as a
 * miss. At WOM's anonymous rate limit this takes a few minutes; on a 429 the
 * seeder waits the Retry-After delay and carries on.
 */
class LiveWomSeeder extends Seeder
{
    public function run(WiseOldManClient $client, SyncWomPlayer $sync, BackfillWomSeries $backfill): void
    {
        $accounts = Account::query()->orderBy('id')->get();

        if ($accounts->isEmpty()) {
            $this->command->warn('LiveWomSeeder: no accounts found; run AccountSeeder first.');

            return;
        }

        $this->command->info(sprintf('LiveWomSeeder: fetching Wise Old Man data for %d accounts...', $accounts->count()));

        $synced = 0;
        $missed = 0;

        foreach ($accounts as $account) {
            while (true) {
                try {
                    $this->syncAccount($account, $client, $sync, $backfill) ? $synced++ : $missed++;

                    break;
                } catch (WiseOldManRateLimited $e) {
                    $this->command->line(sprintf('  … rate limited, waiting %ds', $e->retryAfter));
                    Sleep::for($e->retryAfter)->seconds();
                } catch (Throwable $e) {
                    $missed++;
                    $this->command->line(sprintf('  ✗ %s — %s', $account->username, $e->getMessage()));

                    break;
                }
            }
        }

        $this->command->info(sprintf('LiveWomSeeder: synced %d, missed %d.', $synced, $missed));
    }

    /**
     * Whether WOM knew the account. A miss leaves the existing row untouched.
     */
    private function syncAccount(Account $account, WiseOldManClient $client, SyncWomPlayer $sync, BackfillWomSeries $backfill): bool
    {
        $details = $client->player($account->username);

        if ($details === null) {
            $this->command->line(sprintf('  – %s — not tracked on Wise Old Man', $account->username));

            return false;
        }

        $player = $account->womPlayer ?? new WomPlayer(['account_id' => $account->id]);

        // Real history replaces the synthetic series outright, rather than
        // interleaving with it on the days WOM has no snapshot for.
        WomDailySnapshot::query()->where('account_id', $account->id)->delete();

        $sync->apply($player, $details);
        $days = $backfill->run($player, $account->username);

        $account->setRelation('womPlayer', $player);
        $this->command->line(sprintf('  ✓ %s — %s exp, %d days of history', $account->username, number_format($player->exp), $days));

        return true;
    }
}
