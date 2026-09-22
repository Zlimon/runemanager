<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuestsRequest;
use App\Models\Quest;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;

class QuestController extends Controller
{
    public function __construct(private RecordFeedEvent $feed) {}

    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     *
     * NB: the design calls for quests in PostgreSQL; the current Quest model lives in
     * MongoDB. Keeping the existing model for now — moving it is a separate wedge.
     */
    public function update(UpdateQuestsRequest $request): JsonResponse
    {
        $account = $request->attributes->get('account');
        $validated = $request->validated();

        $existing = Quest::where('account_id', $account->id)->first();
        $previous = $existing?->quests ?? [];

        $quests = $existing ?? (new Quest)->forceFill(['account_id' => $account->id]);
        $quests->quests = $validated['quests'];
        $quests->save();

        // Emit QUEST_COMPLETE for every quest that flipped to
        // finished. The first-ever snapshot is a baseline only: with no
        // previous state, every already-finished quest would flood the feed.
        if ($existing !== null) {
            $this->feed->recordQuestCompletions(
                $account,
                $previous,
                $validated['quests'],
                $validated['position'] ?? null,
            );
        }

        return response()->json(['data' => $quests]);
    }
}
