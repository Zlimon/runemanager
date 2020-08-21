<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SkillResource;
use App\Account;
class AccountResource extends JsonResource
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
        ];
    }

    public function with($request)
    {
        $skills = Helper::listSkills();

        $stats = [];

        foreach ($skills as $skillName) {
            $stats[$skillName] =  DB::table($skillName)->where('account_id', $this->id)->first();
        }

        return [
            'meta' => [
                'hiscores' => SkillResource::collection(collect($stats)),
            ]
        ];
    }
}
