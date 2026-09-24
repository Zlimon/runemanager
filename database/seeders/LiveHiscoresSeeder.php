<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Services\Hiscores\HiscoresSync;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Opt-in: replaces the synthetic hiscores of every seeded account with a live
 * fetch from the official OSRS hiscores API.
 *
 *   ./vendor/bin/sail artisan db:seed --class=LiveHiscoresSeeder
 *
 * `DatabaseSeeder` deliberately does not call this. The fetch is one sequential
 * HTTP request per account, it needs network, it is not reproducible, and demo
 * names that don't exist on the hiscores 404. Failures are reported per account
 * and never abort the run — the account keeps whatever stats it already had.
 */
class LiveHiscoresSeeder extends Seeder
{
    public function run(HiscoresSync $sync): void
    {
        $accounts = Account::all();

        if ($accounts->isEmpty()) {
            $this->command->warn('LiveHiscoresSeeder: no accounts found; run AccountSeeder first.');

            return;
        }

        $this->command->info(sprintf('LiveHiscoresSeeder: fetching hiscores for %d accounts...', $accounts->count()));

        $synced = 0;
        $missed = 0;

        foreach ($accounts as $account) {
            try {
                $sync->syncForAccount($account);
                $synced++;
            } catch (Throwable $e) {
                $missed++;
                $this->command->line(sprintf('  ✗ %s — %s', $account->username, $e->getMessage()));
            }
        }

        $this->command->info(sprintf('LiveHiscoresSeeder: synced %d, missed %d.', $synced, $missed));
    }
}
