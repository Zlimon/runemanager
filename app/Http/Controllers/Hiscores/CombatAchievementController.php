<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\AccountCombatAchievement;
use App\Support\CombatAchievements;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Combat Achievements leaderboard: total points, with the count of
 * fully completed tiers, ranked highest first.
 */
class CombatAchievementController extends Controller
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
