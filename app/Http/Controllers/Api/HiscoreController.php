<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAccountCollectionLogJob;
use App\Jobs\SyncAccountHiscoresJob;
use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
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
    public function sync(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        SyncAccountHiscoresJob::dispatch($account->id);
        SyncAccountCollectionLogJob::dispatch($account->id);

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
