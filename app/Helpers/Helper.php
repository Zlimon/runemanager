<?php

namespace App\Helpers;

use App\Collection;
use DateTime;

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

        $itemData = file_get_contents($itemData);
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

        array_push($itemData,
            json_decode(file_get_contents('https://www.osrsbox.com/osrsbox-db/items-json/' . $itemId . '.json'), true));

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
        return Collection::where('type', 'boss')->orWhere('type', 'raid')->pluck('name')->toArray();
//         return dd(["abyssal sire", "alchemical hydra", "barrows chests", "bryophyta", "callisto", "cerberus", "chambers of xeric", "chambers of xeric challenge mode", "chaos elemental", "chaos fanatic", "commander zilyana", "corporeal beast", "crazy archaeologist", "dagannoth kings", "dagannoth prime", "dagannoth rex", "dagannoth supreme", "deranged archaeologist", "general graardor", "giant mole","grotesque guardians", "hespori", "kalphite queen", "king black dragon", "kraken", "kreearra", "kril tsutsaroth", "mimic", "the nightmare", "obor", "sarachnis", "scorpia", "skotizo", "the gauntlet", "the corrupted gauntlet", "theatre of blood", "thermonuclear smoke devil", "tzkal zuk", "tztok jad", "venenatis", "vetion", "vorkath", "wintertodt", "zalcano", "zulrah"]);
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
        return 'https://secure.runescape.com/m=hiscore_oldschool' . ($accountType === 'normal' ? '' : '_' . ($accountType === 'ultimate_ironman' ? 'ultimate' : $accountType)) . '/index_lite.ws?player=' . str_replace(' ',
                '%20', $playerName);
    }
}
