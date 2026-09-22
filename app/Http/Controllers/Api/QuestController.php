<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    public function __construct(private RecordFeedEvent $feed) {}

    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     *
     * NB: the design calls for quests in PostgreSQL; the current Quest model lives in
     * MongoDB. Keeping the existing model for now — moving it is a separate wedge.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'quests' => ['required', 'array'],
            'quests.*' => ['required', 'array'],
            'quests.*.0' => ['required', 'string'],  // quest name
            'quests.*.1' => ['required', 'integer'], // status (0 = not started, 1 = in progress, 2 = finished)
            ...RecordFeedEvent::positionRules(),
        ]);

        $existing = Quest::where('account_id', $account->id)->first();
        $previous = $existing?->quests ?? [];

        $quests = $existing ?? (new Quest)->forceFill(['account_id' => $account->id]);
        $quests->quests = $request->input('quests');
        $quests->save();

        // Emit QUEST_COMPLETE for every quest that flipped to
        // finished. The first-ever snapshot is a baseline only: with no
        // previous state, every already-finished quest would flood the feed.
        if ($existing !== null) {
            $this->feed->recordQuestCompletions(
                $account,
                $previous,
                $request->input('quests'),
                $request->input('position'),
            );
        }

        return response()->json(['data' => $quests]);
    }
}
