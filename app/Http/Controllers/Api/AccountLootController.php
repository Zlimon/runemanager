<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Broadcast;
use App\Collection;
use App\Events\AccountEvent;
use App\Events\AccountNewUnique;
use App\Events\AnnouncementAll;
use App\Events\EventAll;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountLootController extends Controller
{
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
            return response($accountUsername . " does not have any registered loot for " . $collection->alias, 404);
        }

        $oldCollection = $collectionLog->getAttributes(); // Old data
        //array_splice($oldCollectionValues, count($oldCollectionValues) - 2, 2); // Remove created_at and updated_at

        // Remove non-collection items
        $oldCollectionValues = array_diff_key($oldCollection, [
            "id" => 0,
            "account_id" => 0,
            "rank" => 0,
            "created_at" => 0,
            "updated_at" => 0
        ]);

        $totalLootValue = 0;

        $priceType = "gePrice"; // TODO gePrice / haPrice -> Admin panel

        foreach ($request->all()["metadata"] as $key => $lootItem) {
            if (!in_array($lootItem["name"], ["kill_count", "rank", "obtained"])) {
                $lootValues[$lootItem["name"]] = $lootItem["quantity"];
                $totalLootValue += $lootItem[$priceType] * $lootItem["quantity"];
            }
        }

        $newCollectionValues = [];

        @$newCollectionValues["kill_count"] = $oldCollectionValues["kill_count"] + 1;

        $uniquesCount = @$oldCollectionValues["obtained"] ?: 0;

        $uniques = [];

        if (isset($lootValues)) {
            // Merge old data and new data and sum the total of common keys
            foreach (array_keys($lootValues + $oldCollectionValues) as $itemName) {
                if (isset($lootValues[$itemName]) && isset($oldCollectionValues[$itemName])) {
                    // If unique loot is detected, increase the total amount of uniques obtained by 1
                    if ($oldCollectionValues[$itemName] == 0) {
                        $uniquesCount++;

                        $uniques[] = $itemName;
                    }

                    $newCollectionValues[$itemName] = (isset($lootValues[$itemName]) ? $lootValues[$itemName] : 0) + (isset($oldCollectionValues) ? $oldCollectionValues[$itemName] : 0);
                }
            }
        }

        $newCollectionValues["obtained"] = $uniquesCount;

        $collectionLog->update($newCollectionValues);

        // Remove non-loot items
        $oldCollection = array_diff_key($oldCollectionValues, [
            "kill_count" => 0,
            "obtained" => 0,
        ]);

        // Remove non-loot items
        $updatedCollection = array_diff_key($newCollectionValues, [
            "id" => 0,
            "account_id" => 0,
            "kill_count" => 0,
            "rank" => 0,
            "obtained" => 0,
            "created_at" => 0,
            "updated_at" => 0
        ]);

        $data = $request->all();
        $data['oldCollection'] = $oldCollection;
        $data['updatedCollection'] = $updatedCollection;

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => $collection->category_id,
            "action" => $request->route()->getName(),
            "data" => $data,
            "total" => $totalLootValue
        ];

        $log = Log::create($logData);

        switch ($log->category_id) {
            case 5:
                $noun = " opened a ";
                break;
            case 6:
                $noun = " finished a game of ... and received ";
                break;
            default:
                $noun = " defeated ";
                break;
        }

        $eventData = [
            "log_id" => $log->id,
            "type" => "event",
            "icon" => $collectionLog->getTable(),
            "message" => $accountUsername . $noun . $collection->alias . "!",
        ];

        $event = Broadcast::create($eventData);

        EventAll::dispatch($event);

        AccountEvent::dispatch($account, $event);

        $unlockedUniquesList = [];

        foreach ($data["metadata"] as $key => $metaData) {
            if (isset($metaData["name"]) && in_array($metaData["name"], $uniques)) {
                $unlockedUniquesList[] = $metaData;
            }
        }

        // If new unique unlocked
        if ($unlockedUniquesList) {
            $data["type"] = "UNIQUE";
            $data["metadata"] = $unlockedUniquesList;
            unset($data["drops"]);
            unset($data["oldCollection"]);
            unset($data["updatedCollection"]);

            $logData = [
                "user_id" => auth()->user()->id,
                "account_id" => $account->id,
                "category_id" => $collection->category_id,
                "action" => $request->route()->getName(),
                "data" => $data
            ];

            $log = Log::create($logData);

            $eventData = [
                "log_id" => $log->id,
                "type" => "event",
                "icon" => $collectionLog->getTable(),
                "message" => $accountUsername . " unlocked " . (count($unlockedUniquesList) == 1 ? " a new unique" : "new uniques") . "!",
            ];

            $event = Broadcast::create($eventData);

            EventAll::dispatch($event);

            AccountEvent::dispatch($account, $event);

            if (!in_array(str_replace('_treasure_trails', '', $collectionLog->getTable()), Helper::listClueScrollTiers())) {
                $unlockedUniquesItemsList = [];
                foreach ($data["metadata"] as $metaData) {
                    $unlockedUniquesItemsList[] = ucfirst(str_replace("_", " ", $metaData["name"]));
                }

                $uniqueItems = implode(", ", $unlockedUniquesItemsList);

                $announcementData = [
                    "log_id" => $log->id,
                    "type" => "announcement",
                    "icon" => $collectionLog->getTable(),
                    "message" => $accountUsername . " unlocked " . (count($unlockedUniquesList) == 1 ? " a new unique" : "new uniques") . ": " . $uniqueItems . " from " . $collection->alias . " at " . $newCollectionValues["kill_count"] . " kills!",
                ];

                $announcement = Broadcast::create($announcementData);

                AnnouncementAll::dispatch($announcement);
            } else if (in_array(str_replace('_treasure_trails', '', $collectionLog->getTable()), Helper::listClueScrollTiers())) {
                // TODO add all "exclusive" Clue Scroll items / 3rd age, Gilded
            }
        // If already unlocked unique, but a unique item drop
        } else if ($data['updatedCollection']) {
            if (!in_array(str_replace('_treasure_trails', '', $collectionLog->getTable()), Helper::listClueScrollTiers())) {
                $unlockedUniquesItemsList = [];
                foreach ($data['updatedCollection'] as $uniqueItem => $amount) {
                    $unlockedUniquesItemsList[] = ucfirst(str_replace("_", " ", $uniqueItem));
                }

                $uniqueItems = implode(", ", $unlockedUniquesItemsList);

                $announcementData = [
                    "log_id" => $log->id,
                    "type" => "announcement",
                    "icon" => $collectionLog->getTable(),
                    "message" => $accountUsername . " has received a " . $uniqueItems . " drop from " . $collection->alias . " at " . $newCollectionValues["kill_count"] . " kills!",
                ];

                $announcement = Broadcast::create($announcementData);

                AnnouncementAll::dispatch($announcement);
            }
        }

        return response("Submitted loot for " . $collection->alias, 200);
    }
}
