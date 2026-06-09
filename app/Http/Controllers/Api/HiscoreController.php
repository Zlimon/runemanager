<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAccountCollectionLogJob;
use App\Jobs\SyncAccountHiscoresJob;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §7 — the plugin asks the server to refresh the account's server-pulled
 * stats: skills/bosses/clues from the official OSRS hiscores, and the collection
 * log from TempleOSRS. Part of the full-account snapshot pushed on login; both
 * are queued because they hit external APIs.
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

        return response()->json(['data' => ['queued' => true]], 202);
    }
}
