<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\Collection;

class AccountLootController extends Controller
{
	public function update($accountUsername, $collectionName, Request $request) {
		$account = Account::where('username', $accountUsername)->first();

		if ($account) {
			$collection = Collection::findByName($collectionName);

			if ($collection) {
				$collectionLog = $collection->model::where('account_id', $account->id)->first();

				if ($collectionLog) {
					$oldValues = $collectionLog->getAttributes(); // Get old data
					//array_splice($oldValues, count($oldValues) - 2, 2); // Remove created_at and updated_at

					$newValues = $request->all();

					$sums = [];

					$sums["kill_count"] = $oldValues["kill_count"] + 1;

					$uniques = $oldValues["obtained"];

					// Merge old data and new data and sum the total of common keys
					foreach (array_keys($newValues + $oldValues) as $lootType) {
						if (isset($newValues[$lootType]) && isset($oldValues[$lootType])) {
							// If unique loot is detected, increase the total amount of uniques obtained by 1
							if ($oldValues[$lootType] == 0) {
								$uniques++;
							}

							$sums[$lootType] = (isset($newValues[$lootType]) ? $newValues[$lootType] : 0) + (isset($oldValues) ? $oldValues[$lootType] : 0);
						}
					}

					$sums["obtained"] = $uniques;

					$collectionLog->update($sums);

					return response()->json($collectionLog, 201);
				} else {
					return response()->json("This account does not have any registered loot for " . $collection->name, 404);
				}
			} else {
				return response()->json("This collection could not be found", 404);
			}
		} else {
			return response()->json("This account could not be found", 404);
		}
	}
}
