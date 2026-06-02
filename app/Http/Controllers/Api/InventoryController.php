<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin.
     *
     * The Account is resolved by the plugin.account middleware (see X-Account-Hash
     * + X-Account-Username headers) and attached to the request.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'inventory' => ['required', 'array', 'max:28'],
            'inventory.*' => ['required', 'array', 'max:2'],
            'inventory.*.0' => ['required', 'integer'],
            'inventory.*.1' => ['required', 'integer'],
        ]);

        $inventory = Inventory::where('account_id', $account->id)->first()
            ?? (new Inventory)->forceFill(['account_id' => $account->id]);

        $inventory->inventory = $request->input('inventory');
        $inventory->save();

        return response()->json(['data' => $inventory]);
    }
}
