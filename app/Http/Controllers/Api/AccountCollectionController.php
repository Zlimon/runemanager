<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Log;
use Illuminate\Http\Request;

class AccountCollectionController extends Controller
{
    // NOT IN USE ATM
    public function index($accountUsername, $collectionType)
    {
        $account = Account::where('username', $accountUsername)->first();

        if ($account) {
            if (in_array($collectionType, ['all', 'boss', 'raid', 'clue', 'minigame', 'other'], true)) {
                if ($collectionType === "all") {
                    $allCollections = Collection::select('name')->where('type', $collectionType)->get();
                }
                $allCollections = Collection::select('name')->where('type', $collectionType)->get();

                // TODO create function
                // This method create a migration file for each collection model in the collections table
                // $listOfS = [];
                // foreach ($allCollections as $key => $collection) {
                // 	$collectionName = $collection->name;
                // 	if ($collectionName[strlen($collectionName) - 1] == "s") {
                // 		$listOfS[$key] = $collectionName;
                // 		$command = "make:migration create_".str_replace(" ", "_", $collectionName)."_table";
                // 	} else {
                // 		$command = "make:migration create_".str_replace(" ", "_", $collectionName)."s_table";
                // 	}

                // 	$execute = Artisan::call($command);
                // }

                $allCollectionLoot = [];
                foreach ($allCollections as $key => $collection) {
                    $findCollection = Collection::where('name', $collection->name)->firstOrFail();

                    $collectionLog = $findCollection->model::where('account_id', $account->id)->first();

                    if (!$collectionLog) {
                        return response()->json("This account does not have any registered loot for " . $collection->name,
                            404);
                    }

                    $allCollectionLoot[$key] = $collectionLog;
                }

                return response()->json($allCollectionLoot, 200);
            } else {
                return response()->json("This collection type could not be found", 404);
            }
        } else {
            return response()->json("This account could not be found", 404);
        }
    }

    public function show($accountUsername, $collectionName)
    {
        $collection = Collection::where('name', $collectionName)->firstOrFail();

        return new CollectionResource($collection->model::where('account_id', Helper::getAccountIdFromUsername($accountUsername))->first());
    }

    public function update($accountUsername, $collectionName, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();
        if (!$account) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 401);
        }

        $collection = Collection::where('alias', $collectionName)->first();
        if (!$collection) {
            return response($collectionName . " is not currently supported", 406);
        }

        $collectionLog = $collection->model::where('account_id', $account->id)->first();

        // If account has no collection entry, create it
        if (is_null($collectionLog)) {
            $collectionLog = new $collection->model;

            $collectionLog->getAttributes();

            foreach ($collectionLog->getFillable() as $fillable) {
                $collectionLog->{$fillable} = 0;
            }

            $collectionLog->account_id = $account->id;

            $collectionLog->save();
        }

        if (!$collectionLog) {
            return response($accountUsername . " does not have any registered collction log for " . $collection->alias, 404);
        }

        foreach ($request->all()["collectionLogItems"] as $lootItem) {
            if (!in_array($lootItem["name"], ["kill_count", "rank", "obtained"])) {
                $lootValues[$lootItem["name"]] = $lootItem["quantity"];
            }
        }

        $collectionLog->kill_count = (int)$request->kill_count;

        $collectionLog->obtained = (int)$request->obtained;

        $collectionLog->update($lootValues);

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        return response("Submitted " . $collection->alias . " collection log", 200);
    }
}
