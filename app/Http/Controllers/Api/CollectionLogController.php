<?php

namespace App\Http\Controllers\Api;

use App\Clients\CollectionLogClient;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Collection;
use App\Models\Item;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CollectionLogController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function user(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'exists:accounts,username'],
            'tabs' => ['required', 'array'],
            'tabs.*' => Rule::in(['Bosses', 'Clues', 'Minigames', 'Other', 'Raids']),
        ]);

        try {
            if (Cache::has('collectionlog_user_' . $request['username'])) {
                $collectionLog = Cache::get('collectionlog_user_' . $request['username']);
            } else {
                $collectionLog = $this->request($request);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        // Only return tabs from collection log based on provided tabs from request
        // Unset the other tabs
        foreach ($collectionLog['collectionLog']['tabs'] as $tabName => $tab) {
            if (!in_array($tabName, $request['tabs'])) {
                unset($collectionLog['collectionLog']['tabs'][$tabName]);
            }
        }

        return response()->json($collectionLog);
    }

    /**
     * @param Request $request
     * @return array
     * @throws GuzzleException
     */
    private function request(Request $request): array
    {
        if (Cache::has('collectionlog_user_' . $request['username'])) {
            return Cache::get('collectionlog_user_' . $request['username']);
        }

        $collectionLogClient = new CollectionLogClient();

        try {
            $response = $collectionLogClient->request('GET', '/collectionlog/user/' . $request['username']);

            $result = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $result['collectionLog']['tabs'] = $this->formatTabResult($result['collectionLog']['tabs']);
        } catch (Exception $e) {
            throw $e;
        }

        Cache::set('collectionlog_user_' . $request['username'], $result, 60 * 24);

        return $result;
    }

    /**
     * @param array $tabs
     * @return array
     */
    private function formatTabResult(array $tabs): array
    {
        $reindexCollection = [];

        foreach ($tabs as $tabName => $tab) {
            foreach ($tab as $collectionName => $collectionRow) {
                $collectionRow['name'] = $collectionName;

                $collection = Collection::whereName($collectionName)->first();

                foreach ($collectionRow['items'] as $key => $item) {
                    $dbItem = Item::where('id', (string)$item['id'])->first();

                    if ($dbItem) {
                        $itemResource = (new ItemResource($dbItem))->resolve();
                        $collectionLogItem = $item;

                        $item = array_merge($itemResource, $collectionLogItem);

                        // Strip out fields not needed for frontend for better caching and performance
                        unset($item['last_updated']);
                        unset($item['incomplete']);
                        unset($item['members']);
                        unset($item['tradeable']);
                        unset($item['tradeable_on_ge']);
                        unset($item['stackable']);
                        unset($item['stacked']);
                        unset($item['noted']);
                        unset($item['noteable']);
                        unset($item['linked_id_item']);
                        unset($item['linked_id_noted']);
                        unset($item['linked_id_placeholder']);
                        unset($item['placeholder']);
                        unset($item['equipable']);
                        unset($item['equipable_by_player']);
                        unset($item['equipable_weapon']);
                        unset($item['cost']);
                        unset($item['lowalch']);
                        unset($item['highalch']);
                        unset($item['weight']);
                        unset($item['buy_limit']);
                        unset($item['quest_item']);
                        unset($item['release_date']);
                        unset($item['duplicate']);
                        unset($item['wiki_name']);
                        unset($item['wiki_url']);
                        unset($item['equipment']);
                        unset($item['weapon']);
                        unset($item['sequence']);

                        $collectionRow['items'][$key] = $item;
                    }
                }

                // $collectionRow['obtained'] = array_count_values(array_column($collectionRow['items'], 'obtained'))[true]; // Does not work as array_count_values does not support boolean values
                $collectionRow['obtained'] = count(array_filter($collectionRow['items'], function ($item) {
                    return $item['obtained'] === true;
                }));
                $collectionRow['total'] = count($collectionRow['items']);

                if ($collection) {
                    $reindexCollection[$tabName][$collection->slug] = $collectionRow;
                } else {
                    // If no slug found, keep the original key
                    $reindexCollection[$tabName][$collectionName] = $collectionRow;
                }
            }
        }

        return $reindexCollection;
    }
}
