<?php

namespace App\Helpers;

use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Account;
use Carbon\Carbon;

class Helper
{
    /**
     * Calculates the next hour.
     *
     * @return
     */
    public static function roundToNextHour() {
        $dateString = date("H:i:s");
        $date = new DateTime($dateString);

        $nextHour = (intval($date->format('H'))+1) % 24 . ":00";
        
        return $nextHour;
    }

    /**
     * Generates a valid random item ID.
     *
     * @return
     */
    public static function randomItemId() {
        $randomItemId = rand(0,15000);

        if (self::verifyItem($randomItemId)) {
            return $randomItemId;
        } else {
            return self::randomItemId();
        }
    }

    /**
     * Verifies wheter the URL exists or not.
     *
     * @return
     */
    public static function verifyUrl($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the content of $url. */
        $response = curl_exec($handle);

        /* Check for errors (content not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verifies wheter the item exists or not.
     *
     * @return
     */
    public static function verifyItem($itemId) {
        $itemData = 'https://www.osrsbox.com/osrsbox-db/items-json/'.$itemId.'.json';

        if (self::verifyUrl($itemData)) {
            $itemData = file_get_contents($itemData);
            $itemData = json_decode($itemData, true);

            if (!$itemData['noted']) {
                return true;
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
     * @param integer $itemId, string $attribute
     * @return
     */
    public static function itemData($itemId, $attribute) {
        $itemData = [];

        array_push($itemData, json_decode(file_get_contents('https://www.osrsbox.com/osrsbox-db/items-json/'.$itemId.'.json'), true));

        return $itemData[0][$attribute];
    }

    /**
     * Returns the account ID for currently logged in user.
     *
     * @return
     */
    public static function sessionAccountId() {
        return Auth::user()->member->first()->user_id;
    }

    public static function listSkills() {
        return ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];
    }

    public static function listClueScrollTiers() {
        return ["all", "beginner", "easy", "medium", "hard", "elite", "master"];
    }

    public static function listBosses() {
        return ["abyssal sire", "alchemical hydra", "barrows chests", "bryophyta", "callisto", "cerberus", "chambers of xeric", "chambers of xeric challenge mode", "chaos elemental", "chaos fanatic", "commander zilyana", "corporeal beast", "crazy archaeologist", "dagannoth kings", "dagannoth prime", "dagannoth rex", "dagannoth supreme", "deranged archaeologist", "general graardor", "giant mole","grotesque guardians", "hespori", "kalphite queen", "king black dragon", "kraken", "kreearra", "kril tsutsaroth", "mimic", "nightmare", "obor", "sarachnis", "scorpia", "skotizo", "the gauntlet", "the corrupted gauntlet", "theatre of blood", "thermonuclear smoke devil", "tzkal zuk", "tztok jad", "venenatis", "vetion", "vorkath", "wintertodt", "zalcano", "zulrah"];
    }

    public static function registerAccount($accountName) {
        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.$accountName;

        if (self::verifyUrl($playerDataUrl)) {
            // Get the $playerDataUrl file content.
            $getPlayerData = file_get_contents($playerDataUrl);

            // Fetch the content from $playerDataUrl.
            $playerStats = explode("\n", $getPlayerData);

            // Convert the CSV file of player stats into an array.
            $playerData = [];
            foreach ($playerStats as $playerStat) {
                $playerData[] = str_getcsv($playerStat);
            }

            $account = Account::create([
                'username' => request('username'),
                'rank' => $playerData[0][0],
                'level' => $playerData[0][1],
                'xp' => $playerData[0][2]
            ]);

            $skills = self::listSkills();

            foreach ($skills as $key => $skill) {
                DB::table($skills[$key])->insert([
                    'account_id' => $account->id,
                    'rank' => $playerData[$key+1][0],
                    'level' => $playerData[$key+1][1],
                    'xp' => $playerData[$key+1][2],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            return $account;
        } else {
            return false;
        }
    }

    public static function accountStats($accountId) {
        $accountSkills = [];

        $skills = self::listSkills();

        foreach ($skills as $skillName) {
            array_push($accountSkills, DB::table($skillName)->where('account_id', $accountId)->get());
        }

        return $accountSkills;
    }

    public static function listAccountTypes() {
        return ["normal", "ironman", "hardcore_ironman", "ultimate_ironman"];
    }

    public static function formatAccountTypeName($accountType) {
        return ucfirst(str_replace('_', ' ', $accountType));
    }

    public static function formatHiscoreUrl($accountType, $playerName) {
        return 'https://secure.runescape.com/m=hiscore_oldschool'.($accountType === 'normal' ? '' : '_'.($accountType === 'ultimate_ironman' ? 'ultimate' : $accountType)).'/index_lite.ws?player='.str_replace(' ', '%20', $playerName);
    }
}
