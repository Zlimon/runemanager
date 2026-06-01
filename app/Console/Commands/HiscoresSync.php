<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\Hiscores\HiscoresSync as HiscoresSyncService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('hiscores:sync {username? : Specific OSRS username to sync; if omitted, syncs every account}')]
#[Description('Fetch the latest hiscores from the official OSRS hiscores API and upsert AccountHiscore rows.')]
class HiscoresSync extends Command
{
    public function handle(HiscoresSyncService $sync): int
    {
        $username = $this->argument('username');

        $accounts = $username
            ? Account::whereUsername($username)->get()
            : Account::all();

        if ($username && $accounts->isEmpty()) {
            $this->error(sprintf('No account found with username "%s".', $username));

            return self::FAILURE;
        }

        if ($accounts->isEmpty()) {
            $this->info('No accounts to sync.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $hiscore = $sync->syncForAccount($account);

                $this->line(sprintf(
                    '  ✓ %s — %d skills, %d activities',
                    $account->username,
                    count($hiscore->entries['skills'] ?? []),
                    count($hiscore->entries['activities'] ?? []),
                ));
                $synced++;
            } catch (GuzzleException|Throwable $e) {
                $this->line(sprintf('  ✗ %s — %s', $account->username, $e->getMessage()));
                $failed++;
            }
        }

        $this->info(sprintf('Synced %d, failed %d.', $synced, $failed));

        return $failed > 0 && $synced === 0 ? self::FAILURE : self::SUCCESS;
    }
}
