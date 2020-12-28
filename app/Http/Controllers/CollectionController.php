<?php

namespace App\Http\Controllers;

use App\Collection;
use Artisan;
use Auth;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function list($collectionType)
    {
        if (in_array($collectionType, ['all', 'boss', 'raid', 'clue', 'minigame', 'other'], true)) {
            if ($collectionType === "all") {
                $collectionList = Collection::select('name')->get();

                return response()->json($collectionList, 200);
            }

            $collectionList = Collection::select('name')->where('type', $collectionType)->get();

            return response()->json($collectionList, 200);
        } else {
            return response()->json("This collection type could not be found", 404);
        }
    }

    public function show($collectionName)
    {
        // $collection = Collection::findByName($collectionName);

        // if ($collection) {
        // 	$collectionLog = $collection->model::get();

        // 	if ($collectionLog) {
        // 		return response()->json($collectionLog, 200);
        // 	} else {
        // 		return response()->json("This account does not have any registered loot for " . $collection->name, 404);
        // 	}
        // } else {
        // 	return response()->json("This collection could not be found", 404);
        // }
    }

    public function update($collectionName, Request $request)
    {
        // TODO collection log updater
    }

    private function store($collectionName, $userId)
    {
        // $collection = Collection::findByName($collectionName);

        // $collectionLoot = new $collection->collection_type;

        // $collectionLoot->user_id = $userId;

        // $collectionLoot->save();

        // return $collectionLoot;
    }
}
