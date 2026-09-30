<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAccountCollectionLogJob;
use App\Jobs\SyncAccountHiscoresJob;
use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\CollectionLog;
use App\Support\Instance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The plugin asks the server to refresh the account's server-pulled
 * stats: skills/bosses/clues from the official OSRS hiscores, the collection
 * log from TempleOSRS, and (when enabled) Wise Old Man. Part of the
 * full-account snapshot pushed on login; all are queued because they hit
 * external APIs.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class HiscoreController extends Controller
{
    /**
     * A fetch newer than this is fresh enough to skip, so a plugin that
     * relogs or world-hops cannot make us hammer the OSRS hiscores or
     * TempleOSRS. The scheduled sweeps call the sync services directly and
     * are not held back by it.
     */
    public const SYNC_COOLDOWN_MINUTES = 5;

    public function sync(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $fresh = now()->subMinutes(self::SYNC_COOLDOWN_MINUTES);

        // Both jobs are also unique per account while queued.
        if (! $account->hiscore()->where('fetched_at', '>=', $fresh)->exists()) {
            SyncAccountHiscoresJob::dispatch($account->id);
        }

        if (! CollectionLog::query()->where('account_id', $account->id)->where('fetched_at', '>=', $fresh)->exists()) {
            SyncAccountCollectionLogJob::dispatch($account->id);
        }

        // Delayed so the official hiscores land first: their XP is what tells
        // the sync whether Wise Old Man is behind. Skipped when the last sync
        // is under an hour old, so relogging does not queue another.
        $womSyncedRecently = $account->womPlayer()->where('synced_at', '>=', now()->subHour())->exists();

        if (Instance::womEnabled() && ! $womSyncedRecently) {
            SyncWomPlayerJob::dispatch($account->id)->delay(now()->addMinutes(2));
        }

        return response()->json(['data' => ['queued' => true]], 202);
    }
}
