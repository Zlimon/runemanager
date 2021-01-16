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

        if ($account) {
            $collection = Collection::where('name', $collectionName)->firstOrFail();

            $collectionLog = $collection->model::where('account_id', $account->id)->first();

            // If account has no collection entry, create it
            if (is_null($collectionLog)) {
                $collectionLog = new $collection->model;

                $collectionLog->getAttributes();

                foreach ($collectionLog->getFillable() as $fillable) {
                    $collectionLog->$fillable = 0;
                }

                $collectionLog->account_id = $account->id;

                $collectionLog->save();
            }

            if ($collectionLog) {
                $oldValues = $collectionLog->getAttributes(); // Get old data
                //array_splice($oldValues, count($oldValues) - 2, 2); // Remove created_at and updated_at

                $newValues = $request->except([
                    "id",
                    "account_id",
                    "kill_count",
                    "rank",
                    "obtained",
                    "created_at",
                    "updated_at"
                ]);

                $sums = [];

                @$sums["kill_count"] = $oldValues["kill_count"] + 1;

                $uniques = @$oldValues["obtained"] ?: 0;

                // Merge old data and new data and sum the total of common keys
                foreach (array_keys($newValues + $oldValues) as $lootType) {
                    if (isset($newValues[$lootType]) && isset($oldValues[$lootType])) {
                        // If unique loot is detected, increase the total amount of uniques obtained by 1
                        if ($oldValues[$lootType] == 0) {
                            $uniques++;

                            $dataJson = '{"collection":' . json_encode([$lootType => 0]) . ',"loot":' . json_encode($newValues) . '}';

                            $data = json_decode($dataJson, true);

                            $logData = [
                                "user_id" => auth()->user()->id,
                                "account_id" => $account->id,
                                "category_id" => $collection->category_id,
                                "data" => $data
                            ];

                            $log = Log::create($logData);

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

                        $sums[$lootType] = (isset($newValues[$lootType]) ? $newValues[$lootType] : 0) + (isset($oldValues) ? $oldValues[$lootType] : 0);
                    }
                }

                $sums["obtained"] = $uniques;

                $collectionLog->update($sums);

                $loot = array_diff_key($sums, [
                    "id" => 0,
                    "account_id" => 0,
                    "kill_count" => 0,
                    "rank" => 0,
                    "obtained" => 0,
                    "created_at" => 0,
                    "updated_at" => 0
                ]);

                $dataJson = '{"collection":' . json_encode($loot) . ',"loot":' . json_encode($newValues) . '}';

                $data = json_decode($dataJson, true);

                $logData = [
                    "user_id" => auth()->user()->id,
                    "account_id" => $account->id,
                    "category_id" => $collection->category_id,
                    "data" => $data
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

                return response()->json($collectionLog, 200);
            } else {
                return response("This account does not have any registered loot for " . $collection->name, 404);
            }
        } else {
            return response("This account is not authenticated with " . auth()->user()->name, 401);
        }
    }
}
