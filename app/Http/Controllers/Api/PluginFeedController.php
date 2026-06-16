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
    ];

    public function store(Request $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(self::TYPES)],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = isset($validated['source']) && $validated['source'] !== ''
            ? ['source' => $validated['source']]
            : [];

        $feed->record($account, $validated['type'], $payload);

        return response()->json(['data' => ['recorded' => true]]);
    }
}
