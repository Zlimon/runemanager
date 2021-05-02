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
            return response("There are no linked accounts", 404);
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
            return response("There are no linked accounts", 404);
        }

        $skillHiscore = $skill->model::with('account')
            ->orderBy('rank')
            ->orderByDesc('xp')
            ->orderByDesc('level')
            ->get()
            ->partition(function ($skill) {
                return $skill->rank > 0;
            })
            ->flatten();

        return HiscoreResource::collection($skillHiscore)
            ->additional([
                'meta' => [
                    'name' => $skill->name,
                    'slug' => $skill->slug,
                    'total_xp' => number_format($skillHiscore->sum('xp')),
                    'average_total_level' => round($skillHiscore->sum('level') / $skillHiscore->count()),
                    'total_max_level' => $skillHiscore->where('level', 99)->count(),
                ]
            ]);
    }

    public function collection(Collection $collection)
    {
        if (!Account::count()) {
            return response("There are no linked accounts", 404);
        }

        $collectionHiscore = $collection->model::with('account')
            ->orderBy('rank')
            ->orderByDesc('kill_count')
            ->get()
            ->partition(function ($skill) {
                return $skill->rank > 0;
            })
            ->flatten();

        if (!sizeof($collectionHiscore)) {
            return response("There are no registered collections for " . $collection->slug, 404);
        }

        $collectionUniques = array_diff_key($collectionHiscore[0]->attributesToArray(), [
            "id" => 0,
            "account_id" => 0,
            "kill_count" => 0,
            "rank" => 0,
            "obtained" => 0,
            "created_at" => 0,
            "updated_at" => 0
        ]);

        return HiscoreResource::collection($collectionHiscore)
            ->additional([
                'meta' => [
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'total_kill_count' => number_format($collectionHiscore->sum('kill_count')),
                    'average_kill_count' => round($collectionHiscore->sum('kill_count') / $collectionHiscore->count()),
                    'total_uniques' => count($collectionUniques),
                ]
            ]);
    }
}
