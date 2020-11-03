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
        $collectionLog = $this->attributesToArray();

        $obtained = isset($collectionLog["obtained"]) ? $collectionLog["obtained"] : 0;

        $collectionLog = array_diff_key($collectionLog, ["id" => 0, "account_id" => 0, "kill_count" => 0, "rank" => 0, "obtained" => 0, "created_at" => 0, "updated_at" => 0]);

        return [
            'kill_count' => $this->kill_count,
            'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
            'obtained' => $obtained,
            'log' => $collectionLog,
            'account' => new AccountResource($this->account),
        ];
    }
}
