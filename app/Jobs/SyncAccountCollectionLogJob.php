<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\CollectionLog\CollectionLogSync;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Refresh an account's collection log from TempleOSRS. Queued (hits
 * an external API); triggered by the plugin's login snapshot and a daily sweep.
 */
class SyncAccountCollectionLogJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * One queued collection log fetch per account: a repeated plugin trigger while
     * one is pending is dropped. The lock lapses after ten minutes in case a
     * worker dies holding it.
     */
    public int $uniqueFor = 600;

    public function __construct(public int $accountId) {}

    public function uniqueId(): string
    {
        return (string) $this->accountId;
    }

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
