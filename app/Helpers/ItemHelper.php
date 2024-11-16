<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemHelper
{
    /**
     * Grabs data about item and return data based on attribute.
     *
     * @return mixed
     */
    public static function itemData(int $itemId, string $attribute)
    {
        $itemData = [];

        $itemData[] = json_decode(file_get_contents('https://www.osrsbox.com/osrsbox-db/items-json/'.$itemId.'.json'), true);

        return $itemData[0][$attribute];
    }

    public static function downloadItemIcon(string $itemName)
    {
        $dir = storage_path().'/app/public/items'; // /storage/items/
        $imgName = str_replace(
            "'",
            '',
            str_replace('-', '_', Str::snake(strtolower($itemName)))
        ).'.png'; // abyssal_whip.png

        if (Storage::disk('items')->exists('items/'.$imgName)) {
            return;
        }

        $handle = curl_init(
            'https://api.osrsbox.com/items?where='.urlencode(
                '{"name":"'.ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        str_replace(
                            '-',
                            ' ',
                            Str::snake(strtolower($itemName))
                        )
                    )
                ).'","duplicate":false}'
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

            if (isset($json['_items'][0])) {
                $url = 'https://www.osrsbox.com/osrsbox-db/items-icons/'.(int) $json['_items'][0]['id'].'.png'; // 4151

                if (! File::exists($dir)) {
                    Storage::disk('items')->makeDirectory('items');
                }

                Storage::disk('items')->put('items/'.$imgName, file_get_contents($url));
            }
        }
    }
}
