<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\WiseOldMan\SyncWomPlayer;
use App\Services\WiseOldMan\WiseOldManRateLimited;
use App\Support\Instance;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Refresh one account from Wise Old Man. Dispatched per account by `wom:sync`,
 * by the plugin's login snapshot, and with `$requestUpdate` when the owner opts
 * in to tracking; a no-op while the integration is off. Only one job per
 * account (and kind) waits in the queue at a time.
 *
 * Rate-limit releases count as attempts, so the job is bounded by time
 * ({@see retryUntil()}) and by real failures ({@see $maxExceptions}) rather
 * than by `$tries`.
 */
class SyncWomPlayerJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $maxExceptions = 3;

    public int $backoff = 600;

    public int $uniqueFor = 21600;

    public function __construct(public int $accountId, public bool $requestUpdate = false) {}

    /**
     * Keyed by kind too, so an owner's opt-in is not swallowed by a routine
     * sync that happens to be queued for the same account.
     */
    public function uniqueId(): string
    {
        return $this->accountId.($this->requestUpdate ? ':update' : '');
    }

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            new RateLimited('wiseoldman'),
            (new WithoutOverlapping((string) $this->accountId))->releaseAfter(60)->expireAfter(300),
        ];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(6);
    }

    public function handle(SyncWomPlayer $sync): void
    {
        if (! Instance::womEnabled()) {
            return;
        }

        $account = Account::find($this->accountId);
        if ($account === null) {
            return;
        }

        try {
            $sync->sync($account, $this->requestUpdate);
        } catch (WiseOldManRateLimited $e) {
            $this->release($e->retryAfter);
        } catch (RequestException $e) {
            Log::info('SyncWomPlayerJob: Wise Old Man request failed', [
                'account_id' => $this->accountId,
                'username' => $account->username,
                'status' => $e->response->status(),
            ]);

            // A 4xx will not change on retry; a 5xx may.
            if ($e->response->serverError()) {
                throw $e;
            }
        } catch (Throwable $e) {
            Log::info('SyncWomPlayerJob: Wise Old Man sync failed', [
                'account_id' => $this->accountId,
                'username' => $account->username,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
