<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\AccountDiary;
use App\Support\Diaries;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SPEC §7 — Achievement Diaries leaderboard: total diary tiers completed
 * (out of 48), with a per-tier breakdown, ranked highest first.
 */
class DiaryHiscoreController extends Controller
{
    public function index(): Response
    {
        $hiscores = AccountDiary::with('account.user')
            ->get()
            ->map(function (AccountDiary $row): array {
                $diaries = $row->diaries ?? [];
                $byTier = Diaries::countByTier($diaries);

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'completed' => Diaries::countCompleted($diaries),
                    'easy' => $byTier['Easy'],
                    'medium' => $byTier['Medium'],
                    'hard' => $byTier['Hard'],
                    'elite' => $byTier['Elite'],
                ];
            })
            ->sortByDesc('completed')
            ->values()
            ->all();

        return Inertia::render('Hiscores/Diaries/Show', [
            'hiscores' => $hiscores,
        ]);
    }
}
