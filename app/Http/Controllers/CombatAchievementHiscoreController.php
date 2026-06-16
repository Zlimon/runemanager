<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\AccountCombatAchievement;
use App\Support\CombatAchievements;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SPEC §7.1 — Combat Achievements leaderboard: total points, with the count of
 * fully completed tiers, ranked highest first.
 */
class CombatAchievementHiscoreController extends Controller
{
    public function index(): Response
    {
        $hiscores = AccountCombatAchievement::with('account.user')
            ->get()
            ->map(function (AccountCombatAchievement $row): array {
                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'points' => $row->points,
                    'tasks_completed' => CombatAchievements::completedTaskCount($row->tiers ?? []),
                    'tiers' => $row->tiers ?? [],
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->all();

        return Inertia::render('Hiscores/CombatAchievements/Show', [
            'hiscores' => $hiscores,
        ]);
    }
}
