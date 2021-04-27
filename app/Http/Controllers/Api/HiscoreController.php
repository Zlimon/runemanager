<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BossHiscoreResource;
use App\Http\Resources\HiscoreResource;
use App\Skill;
use Illuminate\Support\Str;

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
                    'name' => 'Total level',
                    'slug' => 'total',
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
                    'name' => ucfirst($skillName),
                    'slug' => $skillName,
                    'total_xp' => number_format($skill->sum('xp')),
                    'average_total_level' => round($skill->sum('level') / $skill->count()),
                    'total_max_level' => $skill->where('level', 99)->count(),
                ]
            ]);
    }

    public function collection(Collection $collection)
    {
        if (!Account::count()) {
            return response()->json("There are no linked accounts", 404);
        }

        $bossHiscore = $collection->model::with('account')
            ->orderBy('rank')
            ->orderByDesc('kill_count')
            ->get()
            ->partition(function ($skill) {
                return $skill->rank > 0;
            })
            ->flatten();

        if (sizeof($bossHiscore) <= 0) {
            return response()->json("There are no registered collections for " . $collection->alias, 404);
        }

        return BossHiscoreResource::collection($bossHiscore)
            ->additional([
                'meta' => [
                    'name' => $collection->alias,
                    'slug' => Str::slug($collection->name),
                    'total' => number_format($bossHiscore->sum('kill_count')),
                    'average' => round($bossHiscore->sum('kill_count') / $bossHiscore->count()),
                ]
            ]);
    }
}
