<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * SPEC §8.1 — simple notable events the plugin detects in-game (mirroring the
 * official Screenshot plugin): a pet drop, a death, or opening a reward screen.
 * Each becomes a live-feed event; a screenshot is attached separately.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class PluginFeedController extends Controller
{
    /** Event types the plugin may push through this generic endpoint. */
    private const TYPES = [
        FeedEvent::TYPE_PET,
        FeedEvent::TYPE_DEATH,
        FeedEvent::TYPE_REWARD,
        FeedEvent::TYPE_LEVEL_UP,
    ];

    public function store(Request $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(self::TYPES)],
            'source' => ['nullable', 'string', 'max:255'],
            // Level-ups carry the skill + level (every level is stored; the feed
            // UI filters to milestones).
            'skill' => ['required_if:type,level_up', 'string', 'max:40'],
            'level' => ['required_if:type,level_up', 'integer', 'min:1', 'max:126'],
        ]);

        $payload = match ($validated['type']) {
            FeedEvent::TYPE_LEVEL_UP => ['skill' => $validated['skill'], 'level' => (int) $validated['level']],
            FeedEvent::TYPE_REWARD => isset($validated['source']) && $validated['source'] !== ''
                ? ['source' => $validated['source']]
                : [],
            default => [],
        };

        $feed->record($account, $validated['type'], $payload);

        return response()->json(['data' => ['recorded' => true]]);
    }
}
