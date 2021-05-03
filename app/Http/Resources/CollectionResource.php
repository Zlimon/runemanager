<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $collectionUniques = array_diff_key($this->attributesToArray(), [
            "id" => 0,
            "account_id" => 0,
            "kill_count" => 0,
            "rank" => 0,
            "obtained" => 0,
            "created_at" => 0,
            "updated_at" => 0
        ]);

        return [
            'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
            'kill_count' => $this->kill_count,
            'obtained' => $this->obtained,
            'total' => sizeof($collectionUniques),
            'log' => $collectionUniques,
        ];
    }
}
