<?php

namespace App\Http\Resources;

use App\Collection;
use App\Skill;
use Illuminate\Http\Resources\Json\JsonResource;

class HiscoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (in_array($this->getTable(), Skill::get()->pluck('name')->toArray())) {
            $hiscore = new SkillResource($this);
        }

        if (in_array($this->getTable(), Collection::get()->pluck('name')->toArray())) {
            $hiscore = new CollectionResource($this);
        }

        return [
            'id' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->id : $this->account_id),
            'username' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->username : $this->account->username),
            'hiscore' => $hiscore,
        ];
    }
}
