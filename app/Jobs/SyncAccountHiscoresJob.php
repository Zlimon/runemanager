<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\Hiscores\HiscoresSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SPEC §7 — fetch the account's stats (skills, bosses, clues) from the official
 * OSRS hiscores and upsert its AccountHiscore. Queued because it hits an
 * external API; triggered by the plugin's full-account snapshot on login.
 */
class SyncAccountHiscoresJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $accountId) {}

    public function handle(HiscoresSync $sync): void
    {
        $account = Account::find($this->accountId);
        if ($account === null) {
            return;
        }

        try {
            $sync->syncForAccount($account);
        } catch (Throwable $e) {
            // The account may simply not be on the hiscores yet (new/low level);
            // log and let the queue retry rather than failing loudly.
            Log::info('SyncAccountHiscoresJob: hiscores sync failed', [
                'account_id' => $this->accountId,
                'username' => $account->username,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
