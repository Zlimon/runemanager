<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Auth;

use App\User;
use App\Collection;

use Artisan;

class CollectionController extends Controller
{
	public function index(Request $request) {
		$user = $this->getUser($request->header('uuid'));

		if ($user) {
			$allCollections = Collection::get();

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

			$allCollectionsLoot = [];
			foreach ($allCollections as $key => $collection) {
				$findCollection = Collection::findByName($collection->name);

				$collectionLog = $this->getUserCollectionLog($findCollection->collection_type, $user->id);

				if (!$collectionLog) {
					$collectionLog = $this->store($collection->name, $user->id);
				}

				$allCollectionLoot[$key] = $collectionLog;
			}

			return response()->json($allCollectionLoot, 200);
		} else {
			return response()->json("This user could not be found", 404);
		}
	}

	public function show($collectionName, Request $request) {
		$user = $this->getUser($request->header('uuid'));

		if ($user) {
			$collection = Collection::findByName($collectionName);

			$collectionLog = $this->getUserCollectionLog($collection->collection_type, $user->id);

			if ($collectionLog) {
				return response()->json($collectionLog, 200);
			} else {
				$test = $this->store($collectionName, $user->id);

				return response()->json($test, 201);
			}
		} else {
			return response()->json("This user could not be found", 404);
		}
	}

	private function store($collectionName, $userId) {
		$collection = Collection::findByName($collectionName);

		$collectionLoot = new $collection->collection_type;

		$collectionLoot->user_id = $userId;

		$collectionLoot->save();

		return $collectionLoot;
	}

	public function update($collectionName, Request $request) {
		$user = $this->getUser($request->header('uuid'));

		if ($user) {
			$collection = Collection::findByName($collectionName);

			$collectionLog = $this->getUserCollectionLog($collection->collection_type, $user->id);

			if ($collectionLog) {
				$collectionLog->update($request->all());

				return response()->json($collectionLog, 201);
			} else {
				return response()->json("This user does not have any registered loot for this collection", 404);
			}
		} else {
			return response()->json("This user could not be found", 404);
		}
	}

	private function getUser($uuid) {
		return User::where('uuid', $uuid)->first();
	}

	private function getUserCollectionLog($collectionType, $userId) {
		return $collectionType::where('user_id', $userId)->first();
	}
}
