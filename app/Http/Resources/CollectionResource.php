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
        $collectionLog = $this->attributesToArray();

        $collectionLog = array_diff_key($collectionLog, [
            "id" => 0,
            "account_id" => 0,
            "kill_count" => 0,
            "rank" => 0,
            "obtained" => 0,
            "created_at" => 0,
            "updated_at" => 0
        ]);

        return [
            'alias' => Helper::collectionAttribute(str_replace("_", " ", $this->getTable()), 'alias'),
            'kill_count' => $this->kill_count,
            'rank' => (number_format($this->rank) >= 1 ? number_format($this->rank) : "Unranked"),
            'obtained' => $this->obtained,
            'total' => sizeof($collectionLog),
            'log' => $collectionLog,
        ];
    }
}
