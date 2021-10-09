<?php

namespace App\Helpers;

use App\Account;
use App\Collection;
use App\Image;
use App\Skill;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Calculates the next hour.
     *
     * @return
     */
    public static function roundToNextHour()
    {
        $dateString = date("H:i:s");
        $date = new DateTime($dateString);

        $nextHour = (intval($date->format('H')) + 1) % 24 . ":00";

        return $nextHour;
    }

    public static function collectionAttribute($collection, $attribute)
    {
        return Collection::where('name', $collection)->value($attribute);
    }

    /**
     * Generates a valid random item ID.
     *
     * @return
     */
    public static function randomItemId($verify = false)
    {
        $randomItemId = rand(0, 25317);

        if ($verify) {
            if (self::verifyItem($randomItemId)) {
                return $randomItemId;
            } else {
                return self::randomItemId(true);
            }
        }

        return $randomItemId;
    }

    /**
     * Verifies wheter the item exists or not.
     *
     * @return
     */
    public static function verifyItem($itemId)
    {
        $itemData = 'https://www.osrsbox.com/osrsbox-db/items-json/' . $itemId . '.json';

        $itemData = @file_get_contents($itemData);

        if ($itemData === false) {
            return false;
        }

        $itemData = json_decode($itemData, true);

        if (!$itemData['noted']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verifies wheter the URL exists or not.
     *
     * @return
     */
    public static function getPlayerData($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the content of $url. */
        $response = curl_exec($handle);

        /* Check for errors (content not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            $playerStats = explode("\n", $response);

            if (count($playerStats) > 1) {
                /* Convert the CSV file of player stats into an array */
                $playerData = [];
                foreach ($playerStats as $playerStat) {
                    $playerData[] = str_getcsv($playerStat);
                }

                if ($playerData[0][0]) {
                    return $playerData;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Grabs data about item and return data based on attribute.
     *
     * @param integer $itemId , string $attribute
     * @return
     */
    public static function itemData($itemId, $attribute)
    {
        $itemData = [];

        array_push(
            $itemData,
            json_decode(file_get_contents('https://www.osrsbox.com/osrsbox-db/items-json/' . $itemId . '.json'), true)
        );

        return $itemData[0][$attribute];
    }

    public static function listSkills()
    {
        return Skill::pluck('slug')->toArray();
    }

    public static function listBosses($asCollection = false)
    {
        if ($asCollection) {
            return Collection::distinct()->where('category_id', 2)->orWhere('category_id', 3)->get();
        }

        return Collection::distinct()->where('category_id', 2)->orWhere('category_id', 3)->pluck('slug')->toArray();
    }

    public static function listNpcs($asCollection = false)
    {
        if ($asCollection) {
            return Collection::where('category_id', 4)->get();
        }

        return Collection::where('category_id', 4)->pluck('slug')->toArray();
    }

    public static function listClues($asCollection = false)
    {
        if ($asCollection) {
            return Collection::where('category_id', 5)->get();
        }

        return Collection::where('category_id', 5)->pluck('slug')->toArray();
    }

    public static function listClueScrollTiers()
    {
        return ["all", "beginner", "easy", "medium", "hard", "elite", "master"];
    }

    public static function listAccountTypes()
    {
        return ["normal", "ironman", "hardcore_ironman", "ultimate_ironman"];
    }

    public static function formatAccountTypeName($accountType)
    {
        return ucfirst(str_replace('_', ' ', $accountType));
    }

    public static function formatHiscoreUrl($accountType, $playerName)
    {
        return 'https://secure.runescape.com/m=hiscore_oldschool' . ($accountType === 'normal' ? '' : '_' . ($accountType === 'ultimate_ironman' ? 'ultimate' : $accountType)) . '/index_lite.ws?player=' . str_replace(
                ' ',
                '%20',
                $playerName
            );
    }

    public static function downloadItemIcon($itemName)
    {
        $dir = storage_path() . '/app/public/items'; // /storage/items/
        $imgName = str_replace(
                "'",
                "",
                str_replace("-", "_", Str::snake(strtolower($itemName)))
            ) . '.png'; // abyssal_whip.png

        if (Storage::disk('items')->exists('items/'.$imgName)) {
            return;
        }

        $handle = curl_init(
            "https://api.osrsbox.com/items?where=" . urlencode(
                '{"name":"' . ucfirst(
                    str_replace(
                        "_",
                        " ",
                        str_replace(
                            "-",
                            " ",
                            Str::snake(strtolower($itemName))
                        )
                    )
                ) . '","duplicate":false}'
            )
        );
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the content of $url. */
        $response = curl_exec($handle);

        /* Check for errors (content not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            $json = json_decode($response, true);

            if (isset($json["_items"][0])) {
                $url = 'https://www.osrsbox.com/osrsbox-db/items-icons/' . (int)$json["_items"][0]["id"] . '.png'; // 4151

                if (!File::exists($dir)) {
                    Storage::disk('items')->makeDirectory("items");
                }

                Storage::disk('items')->put('items/' . $imgName, file_get_contents($url));
            }
        }
    }

    public static function getCollectionModel(Account $account = null, Collection $collection, $categories = null)
    {
        if ($account) {
            return $collection->model::firstWhere('account_id', $account->id);
        }

        $collection = $collection->model::first();

	    return $collection;
    }

    /**
     * @param $accountUsername
     * @param $accountType
     * @param $userId
     * @return string
     * @throws \Throwable
     */
    public static function createOrUpdateAccount($accountUsername, $accountType, $userId) {
        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(
                ' ',
                '%20',
                $accountUsername
            );

        /* Get the $playerDataUrl file content. */
        $playerData = Helper::getPlayerData($playerDataUrl);

        if (!$playerData) {
            return 'Account "'.$accountUsername.'" does not exist in the official hiscores.';
        }

        try {
            $account = self::createAccount($accountUsername, $accountType, $playerData, $userId);
        } catch (\Exception $e) {
            return 'Could not create account "'.$accountUsername.'" due to an unknown error.';
        }

        try {
            self::createOrUpdateAccountHiscores($account, $playerData);
        } catch (\Exception $e) {
            return 'Could not create account "'.$accountUsername.'" due to an unknown error.';
        }

        return $account;
    }

    public static function createAccount($accountUsername, $accountType, $playerData, $userId) {
        try {
            $account = new Account();

            $account->user_id = $userId;
            $account->account_type = $accountType;
            $account->username = $accountUsername;
            $account->rank = $playerData[0][0];
            $account->level = $playerData[0][1];
            $account->xp = $playerData[0][2];

            $account->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $account;
    }

    public static function createOrUpdateAccountHiscores(Account $account, $playerData, $update = false)
    {
        $skills = Skill::get();
        $skillsCount = count($skills);

        foreach ($skills as $key => $skill) {
            if ($update) {
                $skill = $account->skill($skill)->first();
            } else {
                $skill = new $skill->model;
            }

            $skill->account_id = $account->id;
            $skill->rank = ($playerData[$key + 1][0] >= 1 ? $playerData[$key + 1][0] : 0);
            $skill->level = $playerData[$key + 1][1];
            $skill->xp = ($playerData[$key + 1][2] >= 0 ? $playerData[$key + 1][2] : 0);

            try {
                $skill->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

        $clues = Helper::listClueScrollTiers();
        $cluesCount = count($clues);
        $cluesIndex = 0;

        for ($i = ($skillsCount + 3); $i < ($skillsCount + 3 + $cluesCount); $i++) {
            $clueCollection = Collection::where('slug', $clues[$cluesIndex] . '-treasure-trails')->first();

            if ($update) {
                $clueCollection = $account->collection($clueCollection)->first();
            } else {
                $clueCollection = new $clueCollection->model;
            }

            $clueCollection->account_id = $account->id;
            $clueCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $clueCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            try {
                $clueCollection->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            $cluesIndex++;
        }

        $bosses = Helper::listBosses();
        array_splice($bosses, 13, 1);
        $bossIndex = 0;

        $dksKillCount = 0;

        for ($i = ($skillsCount + $cluesCount + 5); $i < ($skillsCount + $cluesCount + 5 + count($bosses)); $i++) {
            $bossCollection = Collection::where('slug', $bosses[$bossIndex])->first();

            if ($update) {
                $bossCollection = $account->collection($bossCollection)->first();
            } else {
                $bossCollection = new $bossCollection->model;
            }

            $bossCollection->account_id = $account->id;
            $bossCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $bossCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            if (in_array(
                $bosses[$bossIndex],
                ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'],
                true
            )) {
                $dksKillCount += ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            }

            try {
                $bossCollection->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            $bossIndex++;
        }

        /**
         * Since there are no official total kill count hiscore for
         * DKS' and we are going to retrieve loot for them from the
         * collection log, we have to manually create a table.
         * This might also happen with other bosses in the future
         * that share collection log entry, but have separate hiscores.
         */
        if ($update) {
            $dks = $account->collection(Collection::where('slug', 'dagannoth-kings')->first())->first();
        } else {
            $dks = new \App\Boss\DagannothKings;
        }

        $dks->account_id = $account->id;
        $dks->kill_count = $dksKillCount;

        try {
            $dks->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        $npcs = Helper::listNpcs();

        foreach ($npcs as $npc) {
            $npcCollection = Collection::findByNameAndCategory($npc, 4);

            if ($update) {
                $npcCollection = $account->collection($npcCollection)->first();
            } else {
                $npcCollection = new $npcCollection->model;
            }

            $npcCollection->account_id = $account->id;

            try {
                $npcCollection->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

        return true;
    }

    public static function imageUpload($imageFile) {
		if ($imageFile == null) {
            return 1;
        }

        $validator = Validator::make($imageFile, [
            'image' => 'mimes:jpeg,bmp,png,gif',
        ]);

        if ($validator->fails()) {
            return false;
        }

        $imageFileName = Str::uuid()->toString();

        $image = Image::create([
            'image_file_name' => $imageFileName,
            'image_file_extension' => $imageFile->getClientOriginalExtension(),
            'image_file_type' => $imageFile->getMimeType(),
            'image_file_size' => $imageFile->getSize()
        ]);

        if (!$image) {
            return false;
        }

        $imageFile->move('storage', $imageFileName.'.'.$imageFile->getClientOriginalExtension());

        return $image->id;
	}
}
