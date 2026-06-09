<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\CollectionLog\CollectionLogSync as CollectionLogSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('collection-log:sync {username? : Specific OSRS username to sync; if omitted, syncs every account}')]
#[Description('Fetch collection logs from TempleOSRS and upsert CollectionLog documents.')]
class CollectionLogSync extends Command
{
    public function handle(CollectionLogSyncService $sync): int
    {
        $username = $this->argument('username');

        $accounts = $username
            ? Account::whereUsername($username)->get()
            : Account::all();

        if ($username && $accounts->isEmpty()) {
            $this->error(sprintf('No account found with username "%s".', $username));

            return self::FAILURE;
        }

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $log = $sync->syncForAccount($account);

                if ($log === null) {
                    $this->line(sprintf('  – %s — not synced on TempleOSRS', $account->username));
                    $skipped++;

                    continue;
                }

                $this->line(sprintf('  ✓ %s — %d / %d slots', $account->username, $log->obtained, $log->total));
                $synced++;
            } catch (Throwable $e) {
                $this->line(sprintf('  ✗ %s — %s', $account->username, $e->getMessage()));
                $failed++;
            }
        }

        $this->info(sprintf('Synced %d, skipped %d, failed %d.', $synced, $skipped, $failed));

        return $failed > 0 && $synced === 0 ? self::FAILURE : self::SUCCESS;
    }
}
