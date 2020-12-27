<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\Collection;

class AccountCollectionController extends Controller
{
	// NOT IN USE ATM
	public function index($accountUsername, $collectionType) {
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
					$findCollection = Collection::findByName($collection->name);

					$collectionLog = $findCollection->model::where('account_id', $account->id)->first();

					if (!$collectionLog) {
						return response()->json("This account does not have any registered loot for " . $collection->name, 404);
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

	public function show($accountUsername, $collectionName) {
		$account = Account::where('username', $accountUsername)->first();

		if ($account) {
			$collection = Collection::findByName($collectionName);

			if ($collection) {
				$collectionLog = $collection->model::where('account_id', $account->id)->first();

				if ($collectionLog) {
					return response()->json($collectionLog, 200);
				} else {
					return response("This account does not have any registered loot for " . $collection->name, 404);
				}
			} else {
				return response("This collection could not be found", 404);
			}
		} else {
			return response("This account is not authenticated with " . auth()->user()->name, 401);
		}
	}

	public function update($accountUsername, $collectionName, Request $request) {
		$account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

		if ($account) {
			$collection = Collection::findByName($collectionName);

			if ($collection) {
				$collectionLog = $collection->model::where('account_id', $account->id)->first();

				if ($collectionLog) {
					$collectionLog->update($request->all());

					return response()->json($collectionLog, 200);
				} else {
					return response("This account does not have any registered loot for " . $collection->name, 404);
				}
			} else {
				return response("This collection could not be found", 404);
			}
		} else {
			return response("This account is not authenticated with " . auth()->user()->name, 401);
		}
	}
}
