<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedEventRequest;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;

/**
 * Simple notable events the plugin detects in-game (mirroring the
 * official Screenshot plugin): a pet drop, a death, or opening a reward screen.
 * Each becomes a live-feed event; a screenshot is attached separately.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class PluginFeedController extends Controller
{
    public function store(StoreFeedEventRequest $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validated();

        $payload = match ($validated['type']) {
            FeedEvent::TYPE_LEVEL_UP => ['skill' => $validated['skill'], 'level' => (int) $validated['level']],
            FeedEvent::TYPE_REWARD => isset($validated['source']) && $validated['source'] !== ''
                ? ['source' => $validated['source']]
                : [],
            default => [],
        };

        $feed->record($account, $validated['type'], $payload, $validated['position'] ?? null);

        return response()->json(['data' => ['recorded' => true]]);
    }
}
