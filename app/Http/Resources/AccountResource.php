<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return (new UserResource($this->user))->resolve();
            }),
            'account_type' => $this->account_type,
            'username' => $this->username,
            'rank' => $this->rank,
            'level' => $this->level,
            'xp' => $this->xp,
            'online' => $this->online,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
