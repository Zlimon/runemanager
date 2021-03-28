<?php

namespace App\Helpers;

use App\Collection;
use DateTime;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        return [
            "attack",
            "defence",
            "strength",
            "hitpoints",
            "ranged",
            "prayer",
            "magic",
            "cooking",
            "woodcutting",
            "fletching",
            "fishing",
            "firemaking",
            "crafting",
            "smithing",
            "mining",
            "herblore",
            "agility",
            "thieving",
            "slayer",
            "farming",
            "runecrafting",
            "hunter",
            "construction"
        ];
    }

    public static function listClueScrollTiers()
    {
        return ["all", "beginner", "easy", "medium", "hard", "elite", "master"];
    }

    public static function listBosses()
    {
        return Collection::distinct()->where('category_id', 2)->orWhere('category_id', 3)->pluck('name')->toArray();
    }

    public static function listNpcs()
    {
        return Collection::where('category_id', 4)->pluck('name')->toArray();
    }

    public static function listClues()
    {
        return Collection::where('category_id', 5)->pluck('name')->toArray();
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
}
