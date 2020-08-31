<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Helpers\Helper;
use App\Account;
use App\Collection;

use App\Http\Resources\HiscoreResource;
use App\Http\Resources\BossHiscoreResource;

class HiscoreController extends Controller
{
    /**
     * Show the application hiscores.
     *
     * @return
     */
    public function skill($skillName) {
        if (Account::count() > 0) {
            if ($skillName == "overall") {
                $hiscores = Account::orderByRaw('CASE WHEN rank > 0 THEN 1 ELSE 2 END')->orderBy('rank', 'ASC')->orderBy('level', 'DESC')->orderBy('xp', 'DESC')->get();

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
                    ->select($skillName.'.account_id', $skillName.'.level', $skillName.'.xp', $skillName.'.rank', 'username')
                    ->join('accounts', $skillName.'.account_id', '=', 'accounts.id')
                    ->orderByRaw('CASE WHEN '.$skillName.'.rank > 0 THEN 1 ELSE 2 END')
                    ->orderBy('rank', 'ASC')
                    ->orderBy('level', 'DESC')
                    ->orderBy('xp', 'DESC')
                    ->get();
            }

        	return HiscoreResource::collection($hiscores)
            	->additional(['meta' => [
            		'skill' => ucfirst($skillName),
                    'total_xp' => number_format($sumTotalXp),
                    'average_total_level' => round($averageTotalLevel),
                    'total_max_level' => $totalMaxLevel,
                	]]);
        } else {
            return response()->json("There are no linked accounts", 404);
        }
    }

    public function boss($bossName) {
        if (Account::count() > 0) {
            $collection = Collection::findByName($bossName);

            $boss = $collection->collection_type::with('account')->orderBy('kill_count', 'DESC')->get();

            $bosses = Helper::listBosses();

            return BossHiscoreResource::collection($boss);
        } else {
            return response()->json("There are no linked accounts", 404);
        }
    }
}
