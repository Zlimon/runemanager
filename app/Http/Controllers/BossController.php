<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Collection;

class BossController extends Controller
{
	public function update($bossName, Request $request) {
		$user = $this->getUser($request->header('uuid'));

		if ($user) {
			$boss = Collection::findByName($bossName);

			if ($boss) {
				$bossLog = $this->getUserBossLog($boss->collection_type, $user->id);

				if ($bossLog) {
					$oldValues = $bossLog->getAttributes(); // Get old data
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

					$bossLog->update($sums);

					return response()->json($bossLog, 201);
				} else {
					return response()->json("This user does not have any registered loot for this boss", 404);
				}
			} else {
				return response()->json("This boss does not exist", 404);
			}
		} else {
			return response()->json("This user could not be found", 404);
		}
	}

	private function getUser($uuid) {
		return User::where('uuid', $uuid)->first();
	}

	private function getUserBossLog($boss, $userId) {
		return $boss::where('user_id', $userId)->first();
	}
}
