<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Http\Resources\AccountResource;
use App\Models\AccountDiary;
use App\Services\Hiscores\LeaderboardPage;
use App\Support\Diaries;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Achievement Diaries leaderboard: total diary tiers completed
 * (out of 48), with a per-tier breakdown, ranked highest first.
 */
class DiaryController extends Controller
{
    public function index(FilterHiscoresRequest $request, LeaderboardPage $page): Response
    {
        $hiscores = $page->rankedBy(
            AccountDiary::with('account.user')
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
                ->all(),
            'completed',
        );

        return Inertia::render('Hiscores/Diaries/Show', [
            'hiscores' => $page->of($hiscores, $request),
            'filters' => $request->filters(),
        ]);
    }
}
