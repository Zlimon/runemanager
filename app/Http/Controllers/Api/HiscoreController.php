<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BossHiscoreResource;
use App\Http\Resources\HiscoreResource;
use Illuminate\Support\Facades\DB;

class HiscoreController extends Controller
{
    /**
     * Show the application hiscores.
     *
     * @return
     */
    public function skill($skillName)
    {
        if (Account::count() > 0) {
            if ($skillName == "overall") {
                $hiscores = Account::orderByRaw('CASE WHEN rank > 0 THEN 1 ELSE 2 END')->orderBy('rank')->orderByDesc('level')->orderByDesc('xp')->get();

                $sumTotalXp = Account::sum('xp');

                $averageTotalLevel = Account::sum('level') / Account::count();

                $skills = Helper::listSkills();

                $totalMaxLevel = Account::where('level', (99 * count($skills)))->count();
            } else {
                $sumTotalXp = DB::table($skillName)
                    ->selectRaw('SUM(xp) AS total_xp')
                    ->first();

                $sumTotalXp = $sumTotalXp->total_xp;

                $sumTotalLevel = DB::table($skillName)
                    ->selectRaw('SUM(level) AS total_level')
                    ->selectRaw('COUNT(*) AS total_hiscores')
                    ->first();

                $averageTotalLevel = $sumTotalLevel->total_level / $sumTotalLevel->total_hiscores;

                $totalMaxLevel = DB::table($skillName)
                    ->selectRaw('COUNT(*) AS amount_99')
                    ->where('level', 99)
                    ->first();

                $totalMaxLevel = $totalMaxLevel->amount_99;

                $hiscores = DB::table($skillName)
                    ->select($skillName . '.account_id', $skillName . '.level', $skillName . '.xp',
                        $skillName . '.rank', 'username')
                    ->join('accounts', $skillName . '.account_id', '=', 'accounts.id')
                    ->orderByRaw('CASE WHEN ' . $skillName . '.rank > 0 THEN 1 ELSE 2 END')
                    ->orderBy('rank')
                    ->orderByDesc('level')
                    ->orderByDesc('xp')
                    ->get();
            }

            return HiscoreResource::collection($hiscores)
                ->additional([
                    'meta' => [
                        'skill' => ucfirst($skillName),
                        'total_xp' => number_format($sumTotalXp),
                        'average_total_level' => round($averageTotalLevel),
                        'total_max_level' => $totalMaxLevel,
                    ]
                ]);
        } else {
            return response()->json("There are no linked accounts", 404);
        }
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
}
