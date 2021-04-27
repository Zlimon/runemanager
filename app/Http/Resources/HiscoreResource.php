<?php

namespace App\Http\Resources;

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
        return [
            'id' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->id : $this->account_id),
            'username' => ($request->getRequestUri() === "/api/hiscore/skill/total" ? $this->username : $this->account->username),
            'hiscore' => new SkillResource($this)
        ];
    }
}
