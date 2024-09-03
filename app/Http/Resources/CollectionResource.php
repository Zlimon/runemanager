<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
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
            'account' => $this->whenLoaded('account', function () {
                return (new AccountResource($this->account))->resolve();
            }),
            'rank' => $this->rank,
            'kill_count' => $this->kill_count,
            'obtained' => $this->obtained,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
