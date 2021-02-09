<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AccountSkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'account_type' => $this->account_type,
            'username' => $this->username,
            'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
            'level' => $this->level,
            'xp' => (number_format($this->xp) >= 1 ? number_format($this->xp) : "Unranked"),
            'online' => $this->online == 0 ? "Offline" : "Online",
            'joined' => date_format($this->created_at, "d. M Y"),
            'user' => new UserResource($this->user),
        ];
    }

    public function with($request)
    {
        $skills = Helper::listSkills();

        $skillHiscores = [];

        foreach ($skills as $skillName) {
            $skillHiscores[$skillName] = DB::table($skillName)->where('account_id', $this->id)->first();
        }

        return [
            'meta' => [
                'skillHiscores' => SkillResource::collection(collect($skillHiscores)),
            ]
        ];
    }
}
