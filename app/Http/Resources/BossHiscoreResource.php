<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BossHiscoreResource extends JsonResource
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
            'kill_count' => $this->kill_count,
            'account' => new AccountResource($this->account),
            //'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
        ];
    }
}
