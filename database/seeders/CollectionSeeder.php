<?php

namespace Database\Seeders;

use App\Clients\CollectionLogClient;
use App\Helpers\HiscoreHelper;
use App\Models\Category;
use App\Models\Item;
use App\Traits\CollectionTrait;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    use CollectionTrait;

    /**
     * Run the database seeds.
     */
//    public function run(): void
//    {
//        foreach ($this->hiscoreEntries as $category => $hiscore) {
//            $category = Category::whereSlug($category)->first();
//
//            if (!$category) {
//                $this->command->warn(sprintf("Category '%s' does not exist.", $category));
//
//                continue;
//            }
//
//            foreach ($hiscore as $slug => $name) {
//                try {
//                    $this->createHiscore($category, $name);
//                } catch (Exception $e) {
//                    $this->command->warn($e->getMessage());
//
//                    continue;
//                }
//            }
//        }
//    }

    public function run(): void
    {
        $collectionLogClient = new CollectionLogClient();

        // Get number 1 player on collectionlog.net hiscores to get all collection log pages
        try {
            $response = $collectionLogClient->request('GET', '/hiscores/1?accountType=NORMAL');

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['hiscores'][0]['username'])) {
                throw new Exception('Could not retrieve rank 1 player from collectionlog.net hiscores.');
            }
        } catch (Exception $e) {
            $this->command->warn($e->getMessage());

            return;
        }

        $rankOne = $result['hiscores'][0]['username'];

        // Get all collection log entries for rank 1 player
        try {
            $response = $collectionLogClient->request('GET', '/collectionlog/user/' . $rankOne);

            $result = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            $this->command->warn($e->getMessage());

            return;
        }

        $hiscoreEntries = HiscoreHelper::all();
        $storeItems = false;
        foreach ($result['collectionLog']['tabs'] as $category => $hiscores) {
            // collectionLogTab is the collection name on collectionlog.net
            $category = match ($category) {
                'Clues' => 'clue',
                'Minigames' => 'minigame',
                'Bosses' => 'boss',
                'Raids' => 'raid',
                'Other' => 'other',
                default => $category,
            };

            $category = Category::whereSlug($category)->first();

            if (!$category) {
                $this->command->warn(sprintf("Category '%s' does not exist.", $category));

                continue;
            }

            foreach ($hiscores as $name => $hiscore) {
                // Unset entries already fetched from collectionlog.net as we only want to create hiscore entries unique to OSRS hiscores
                unset($hiscoreEntries[$category->slug][Str::slug($name)]);

                $items = [];
                if ($storeItems && !empty($hiscore['items'])) {
                    // Map items from Item model
                    $itemIds = array_map(function ($item) {
                        return (string) $item['id'];
                    }, $hiscore['items']);

//                    $items = Item::whereIn('id', $itemIds)->pluck('name')->map(function ($item) {
//                        return Str::slug(Str::snake($item), '_');
//                    })->toArray();
//
//                    $items = Item::whereIn('id', $itemIds)->orderBy('id')->get()->toArray();
                    // Get items from Item model in same order the items are in the collection log
                    foreach ($itemIds as $itemId) {
                        $item = Item::whereId($itemId)->first();

                        if ($item) {
                            $items[] = $item;
                        }
                    }
                }

                try {
                    $collection = $this->createHiscore($category, $name, $storeItems ? $items : []);
                } catch (Exception $e) {
                    $this->command->warn($e->getMessage());

                    continue;
                }
            }
        }

        // Create separate collections for hiscore entries not used in collectionlog.net
        foreach ($hiscoreEntries as $category => $hiscores) {
            $category = Category::whereSlug($category)->first();

            if (!$category) {
                $this->command->warn(sprintf("Category '%s' does not exist.", $category));

                continue;
            }

            foreach ($hiscores as $name) {
                try {
                    $modelCreated = $this->createModel($category, $name, []);
                } catch (Exception $e) {
                    $this->command->warn($e->getMessage());

                    continue;
                }
            }
        }
    }
}
