<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnlockCombatAchievementRequest;
use App\Http\Requests\UpdateCombatAchievementsRequest;
use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Services\Feed\RecordFeedEvent;
use App\Support\CombatAchievements;
use Illuminate\Http\JsonResponse;

/**
 * Combat Achievements push from the plugin.
 *
 * Two entry points: a snapshot upsert of total points + per-tier status, and a
 * live task-unlock that records a feed event (the plugin parses the in-game
 * completion message). The Account is resolved by the plugin.account middleware.
 */
class CombatAchievementController extends Controller
{
    /** Snapshot upsert: total points + per-tier status. */
    public function update(UpdateCombatAchievementsRequest $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validated();

        $tiers = CombatAchievements::normaliseTiers((array) $validated['tiers']);

        AccountCombatAchievement::updateOrCreate(
            ['account_id' => $account->id],
            ['points' => $validated['points'], 'tiers' => $tiers],
        );

        broadcast(new AccountDataUpdated($account, 'combat_achievements'));

        return response()->json(['data' => [
            'points' => $validated['points'],
            'tasks_completed' => CombatAchievements::completedTaskCount($tiers),
        ]]);
    }

    /** Live unlock: record a feed event for a freshly completed task. */
    public function unlock(UnlockCombatAchievementRequest $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validated();

        $feed->recordCombatAchievement(
            $account,
            $validated['task'],
            $validated['tier'] ?? null,
            $validated['position'] ?? null,
        );

        return response()->json(['data' => ['recorded' => true]]);
    }
}
