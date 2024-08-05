<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account' => (new AccountResource($this->whenLoaded('account'))),
            'rank' => $this->rank,
            'level' => $this->level,
            'xp' => $this->xp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
