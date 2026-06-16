<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FeedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * SPEC §8 — a clean screenshot of the moment, uploaded by the plugin and
 * attached to the matching feed event. Because feed events are created
 * server-side a beat earlier (from the loot/CA/collection-log push), we attach
 * to the most recent event of this account + type that doesn't already have one.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class FeedScreenshotController extends Controller
{
    /** How recent a matching event must be to receive a screenshot. */
    private const MATCH_WINDOW_SECONDS = 120;

    private const SCREENSHOT_TYPES = [
        FeedEvent::TYPE_LOOT_DROP,
        FeedEvent::TYPE_COMBAT_ACHIEVEMENT,
        FeedEvent::TYPE_COLLECTION_LOG,
        FeedEvent::TYPE_PET,
        FeedEvent::TYPE_DEATH,
        FeedEvent::TYPE_REWARD,
    ];

    public function store(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $request->validate([
            'type' => ['required', 'string', Rule::in(self::SCREENSHOT_TYPES)],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $event = FeedEvent::query()
            ->where('account_id', $account->id)
            ->where('type', $request->query('type', $request->input('type')))
            ->whereNull('screenshot_path')
            ->where('created_at', '>=', now()->subSeconds(self::MATCH_WINDOW_SECONDS))
            ->latest('id')
            ->first();

        // No qualifying event (e.g. the drop was below the feed value floor) —
        // quietly discard the screenshot rather than orphan it.
        if ($event === null) {
            return response()->json(['data' => ['attached' => false]]);
        }

        $path = $request->file('image')->storeAs(
            'feed-screenshots',
            $event->id.'.'.$request->file('image')->extension(),
            'public',
        );

        $event->update(['screenshot_path' => $path]);

        return response()->json(['data' => ['attached' => true]]);
    }
}
