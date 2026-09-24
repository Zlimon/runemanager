<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin.
     *
     * The Account is resolved by the plugin.account middleware (see X-Account-Hash
     * + X-Account-Username headers) and attached to the request.
     */
    public function update(UpdateInventoryRequest $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $inventory = Inventory::where('account_id', $account->id)->first()
            ?? (new Inventory)->forceFill(['account_id' => $account->id]);

        $inventory->inventory = $request->validated('inventory');
        $inventory->save();

        broadcast(new AccountDataUpdated($account, 'inventory'));

        return response()->json(['data' => $inventory]);
    }
}
