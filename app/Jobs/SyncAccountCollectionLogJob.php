<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\CollectionLog\CollectionLogSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SPEC §5.2 — refresh an account's collection log from TempleOSRS. Queued (hits
 * an external API); triggered by the plugin's login snapshot and a daily sweep.
 */
class SyncAccountCollectionLogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $accountId) {}

    public function handle(CollectionLogSync $sync): void
    {
        $account = Account::find($this->accountId);
        if ($account === null) {
            return;
        }

        try {
            $sync->syncForAccount($account);
        } catch (Throwable $e) {
            Log::info('SyncAccountCollectionLogJob: failed', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
