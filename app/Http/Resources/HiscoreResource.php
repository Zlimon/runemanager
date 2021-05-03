<?php

namespace App\Http\Resources;

use App\Collection;
use App\Skill;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
        if (in_array(Str::slug($this->getTable()), Skill::get()->pluck('slug')->toArray()) || $this->getTable() === 'accounts') {
            $hiscore = new SkillResource($this);
        }

        if (in_array(Str::slug($this->getTable()), Collection::get()->pluck('slug')->toArray())) {
            $hiscore = new CollectionResource($this);
        }

        if (!$hiscore) {
            abort(404);
        }

        return [
            'id' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->id : $this->account_id),
            'username' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->username : $this->account->username),
            'hiscore' => $hiscore,
        ];
    }
}
