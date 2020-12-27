<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Helpers\Helper;
use App\Collection;

class AccountBossResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'username' => $this->username,
            'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
            'level' => $this->level,
            'xp' => (number_format($this->xp) >= 1 ? number_format($this->xp) : "Unranked"),
            'joined' => date_format($this->created_at, "d. M Y"),
            'user' => new UserResource($this->user),
        ];
    }

    public function with($request)
    {
        $bosses = Helper::listBosses();

        $bossHiscores = [];

        foreach ($bosses as $bossName) {
            $collection = Collection::findByName($bossName);

            $bossHiscores[$bossName] = $collection->model::where('account_id', $this->id)->first();

            $bossHiscores[$bossName]["boss_name"] = $bossName;
        }

        return [
            'meta' => [
                'bossHiscores' => BossResource::collection(collect($bossHiscores)),
            ]
        ];
    }
}
