<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\Collection;
use App\Models\Image;
use App\Skill;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ItemHelper
{
    /**
     * Generates a valid random item ID.
     *
     * @param bool $verify
     * @return int
     */
    public static function randomItemId(bool $verify = false): int
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
     * Verifies whether the item exists or not.
     *
     * @param $itemId
     * @return bool
     */
    public static function verifyItem($itemId): bool
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
     * Grabs data about item and return data based on attribute.
     *
     * @param integer $itemId
     * @param string $attribute
     * @return mixed
     */
    public static function itemData(int $itemId, string $attribute)
    {
        $itemData = [];

        $itemData[] = json_decode(file_get_contents('https://www.osrsbox.com/osrsbox-db/items-json/' . $itemId . '.json'), true);

        return $itemData[0][$attribute];
    }

    public static function monsterItemsData(string $monsterName)
    {
        $wikiScraper = new WikiScraper();

        try {
            // Fetch the drops by monster
            $promise = $wikiScraper->getDropsByMonster(ucfirst($monsterName));

            // Wait for the promise to resolve and get the result
            $items = $promise->wait();

            // Process the items as needed
            // For example, you can return or print them
            return $items;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public static function downloadItemIcon(string $itemName)
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

        // Get the content of $url
        $response = curl_exec($handle);

        // Check for errors (content not found)
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        // If the document has loaded successfully without any redirection or error
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
