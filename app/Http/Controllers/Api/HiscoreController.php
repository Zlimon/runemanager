<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BossHiscoreResource;
use App\Http\Resources\HiscoreResource;
use App\Skill;

class HiscoreController extends Controller
{
    public function total() {
        if (!Account::count()) {
            return response()->json("There are no linked accounts", 404);
        }

        $total = Account::orderBy('rank')
            ->orderByDesc('xp')
            ->orderByDesc('level')
            ->get()
            ->partition(function ($skill) {
                return $skill->rank > 0;
            })
            ->flatten();

        return HiscoreResource::collection($total)
            ->additional([
                'meta' => [
                    'skill' => 'total',
                    'name' => 'Total level',
                    'total_xp' => number_format($total->sum('xp')),
                    'average_total_level' => round($total->sum('level') / $total->count()),
                    'total_max_level' => Account::where('level', (99 * count(Helper::listSkills())))->count(),
                ]
            ]);
    }

    /**
     * Show the application hiscores.
     *
     * @return
     */
    public function skill(Skill $skill)
    {
        if (!Account::count()) {
            return response()->json("There are no linked accounts", 404);
        }

        $skill = $skill->model::with('account')
            ->orderBy('rank')
            ->orderByDesc('xp')
            ->orderByDesc('level')
            ->get()
            ->partition(function ($skill) {
                return $skill->rank > 0;
            })
            ->flatten();

        $skillName = $skill[0]->getTable();

        return HiscoreResource::collection($skill)
            ->additional([
                'meta' => [
                    'skill' => $skillName,
                    'name' => ucfirst($skillName),
                    'total_xp' => number_format($skill->sum('xp')),
                    'average_total_level' => round($skill->sum('level') / $skill->count()),
                    'total_max_level' => $skill->where('level', 99)->count(),
                ]
            ]);
    }

    public function boss($bossName)
    {
        if (Account::count() > 0) {
            $collection = Collection::where('name', $bossName)->where(function ($query) {
                $query->where('category_id', 2)
                    ->orWhere('category_id', 3);
            })->firstOrFail();

            $bossHiscore = $collection->model::with('account')->orderByDesc('kill_count')->get();

            if (sizeof($bossHiscore) > 0) {
                $sumKills = $collection->model::selectRaw('SUM(kill_count) AS total_kill_count')
                    ->selectRaw('COUNT(*) AS total_kills')
                    ->first();

                $averageTotalKills = $sumKills["total_kill_count"] / $sumKills["total_kills"];

                return BossHiscoreResource::collection($bossHiscore)
                    ->additional([
                        'meta' => [
                            'boss' => str_replace(" ", "_", $bossName),
                            'alias' => $collection->alias,
                            'total_kills' => number_format($sumKills["total_kill_count"]),
                            'average_total_kills' => round($averageTotalKills),
                        ]
                    ]);
            } else {
                return response()->json("There are no registered collections for " . $bossName, 404);
            }
        } else {
            return response()->json("There are no linked accounts", 404);
        }
    }

    public function npc($npcName)
    {
        if (Account::count() > 0) {
            $collection = Collection::where('name', $npcName)->where('category_id', 4)->firstOrFail();

            $npcHiscore = $collection->model::with('account')->orderByDesc('kill_count')->get();

            if (sizeof($npcHiscore) > 0) {
                $sumKills = $collection->model::selectRaw('SUM(kill_count) AS total_kill_count')
                    ->selectRaw('COUNT(*) AS total_kills')
                    ->first();

                $averageTotalKills = $sumKills["total_kill_count"] / $sumKills["total_kills"];

                return BossHiscoreResource::collection($npcHiscore)
                    ->additional([
                        'meta' => [
                            'npc' => str_replace(" ", "_", $npcName),
                            'alias' => $collection->alias,
                            'total_kills' => number_format($sumKills["total_kill_count"]),
                            'average_total_kills' => round($averageTotalKills),
                        ]
                    ]);
            } else {
                return response()->json("There are no registered collections for " . $npcName, 404);
            }
        } else {
            return response()->json("There are no linked accounts", 404);
        }
    }

    public function clue($clueDifficulty)
    {
        if (Account::count() > 0) {
            $collection = Collection::where('name', $clueDifficulty)->where('category_id', 5)->firstOrFail();

            $npcHiscore = $collection->model::with('account')->orderByDesc('kill_count')->get();

            if (sizeof($npcHiscore) > 0) {
                $sumKills = $collection->model::selectRaw('SUM(kill_count) AS total_kill_count')
                    ->selectRaw('COUNT(*) AS total_kills')
                    ->first();

                $averageTotalKills = $sumKills["total_kill_count"] / $sumKills["total_kills"];

                return BossHiscoreResource::collection($npcHiscore)
                    ->additional([
                         'meta' => [
                             'clue' => str_replace(" ", "_", $clueDifficulty),
                             'alias' => $collection->alias,
                             'total_kills' => number_format($sumKills["total_kill_count"]),
                             'average_total_kills' => round($averageTotalKills),
                         ]
                     ]);
            } else {
                return response()->json("There are no registered collections for " . $clueDifficulty, 404);
            }
        } else {
            return response()->json("There are no linked accounts", 404);
        }
    }
}
