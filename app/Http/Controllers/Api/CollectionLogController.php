<?php

namespace App\Http\Controllers\Api;

use App\Clients\CollectionLogClient;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Account;
use App\Models\Collection;
use App\Models\Item;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CollectionLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     * @throws GuzzleException
     */
    public function index(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'tabs' => ['required', 'array'],
            'tabs.*' => Rule::in(['Bosses', 'Clues', 'Minigames', 'Other', 'Raids']),
        ]);

        try {
            $client = new CollectionLogClient;

            $response = $client->request('GET', "/collectionlog/user/{$account->username}");

            $collectionLog = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        // Only return tabs from collection log based on provided tabs from request
        // Unset the other tabs
        foreach ($collectionLog['collectionLog']['tabs'] as $tabName => $tab) {
            if (! in_array($tabName, $request['tabs'])) {
                unset($collectionLog['collectionLog']['tabs'][$tabName]);
            }
        }

        $tabs = [];
        foreach ($collectionLog['collectionLog']['tabs'] as $tabName => $tab) {
            foreach ($tab as $collectionName => $collection) {
                $slug = Str::slug($collectionName);

                $tabs['collection_log'][$tabName][$slug]['name'] = $collectionName;
                $tabs['collection_log'][$tabName][$slug]['slug'] = $slug;
                $tabs['collection_log'][$tabName][$slug]['obtained'] = count(array_filter($collection['items'],
                    function ($item) {
                        return $item['obtained'] === true;
                    }));
                $tabs['collection_log'][$tabName][$slug]['total'] = count($collection['items']);
            }
        }

        return response()->json($tabs);
    }

    /**
     * Display the specified resource.
     *
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function show(Account $account, string $tab, string $collection): JsonResponse
    {
        Validator::make(['tab' => $tab], [
            'tab' => Rule::in(['Bosses', 'Clues', 'Minigames', 'Other', 'Raids']),
        ])->validate();

        $collection = Collection::whereSlug($collection)->firstOrFail();

        try {
            $client = new CollectionLogClient;

            $response = $client->request('GET', "/items/user/{$account->username}?pageName={$collection->name}");

            $collectionLog = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        $collectionLog['name'] = $collection->name;
        $collectionLog['slug'] = $collection->slug;
        $collectionLog['obtained'] = $collectionLog['obtainedCount'];
        $collectionLog['total'] = $collectionLog['itemCount'];

        // Unset the keys that are not needed
        unset($collectionLog['page'], $collectionLog['obtainedCount'], $collectionLog['itemCount']);

        $items = Item::select('_id', 'name', 'examine', 'icon')->whereIn('_id',
            array_column($collectionLog['items'], 'id'))->get()->keyBy('_id');

        foreach ($collectionLog['items'] as $key => $item) {
            $dbItem = $items[$item['id']]->toArray();

            if ($dbItem) {
                $collectionLog['items'][$key] = array_merge($dbItem, $item);
            }
        }

        return response()->json($collectionLog);
    }

    private function formatTabResult(array $tabs): array
    {
        $reindexCollection = [];

        foreach ($tabs as $tabName => $tab) {
            foreach ($tab as $collectionName => $collectionRow) {
                $collectionRow['name'] = $collectionName;

                $collection = Collection::whereName($collectionName)->first();

                $itemIds = array_column($collectionRow['items'], 'id');
                $items = Item::select('_id', 'name', 'examine', 'icon')->whereIn('_id', $itemIds)->get()->keyBy('_id');

                foreach ($collectionRow['items'] as $key => $item) {
                    $dbItem = $items[$item['id']]->toArray();

                    if ($dbItem) {
                        // $itemResource = (new ItemResource($dbItem))->resolve();
                        $collectionRow['items'][$key] = array_merge($dbItem, $item);
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
