<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §8.1 — a collection-log slot unlock pushed by the plugin (parsed from the
 * in-game "new item added to your collection log" notice). Records a feed event.
 * The full collection log itself is pulled from TempleOSRS, not pushed here.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class CollectionLogController extends Controller
{
    public function unlock(Request $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'item' => ['required', 'string', 'max:255'],
        ]);

        $feed->recordCollectionLogSlot($account, $validated['item']);

        return response()->json(['data' => ['recorded' => true]]);
    }
}
