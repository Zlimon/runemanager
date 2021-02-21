<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Events\AccountAll;
use App\Events\AccountKill;
use App\Events\AccountNewUnique;
use App\Events\All;
use App\Http\Controllers\Controller;
use App\Log;
use App\Notification;
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

        $collection = Collection::where('name', $collectionName)->first();
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

        $notificationData = [
            "log_id" => $log->id,
            "icon" => strtolower(Str::snake($collectionName)),
            "message" => $accountUsername . " defeated " . $collection->alias . "!",
        ];

        $notification = Notification::create($notificationData);

        All::dispatch($notification);

        AccountAll::dispatch($account, $notification);

        AccountKill::dispatch($account, $notification);

        foreach ($uniques as $unique) {
            $notificationData = [
                "log_id" => $log->id,
                "icon" => strtolower(Str::snake($collectionName)),
                "message" => $accountUsername . " unlocked a new unique!",
            ];

            $notification = Notification::create($notificationData);

            All::dispatch($notification);

            AccountAll::dispatch($account, $notification);

            AccountNewUnique::dispatch($account, $notification);
        }

        return response("Submitted loot for " . $collection->alias, 200);
    }
}
