<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;

class EquipmentController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     *
     * The plugin sends `equipment` as a sparse array indexed by OSRS KitType slot. Each
     * present slot is a [itemId, quantity] pair; -1 in position 0 means an empty slot.
     */
    public function update(UpdateEquipmentRequest $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $slots = $request->validated('equipment');

        $equipment = Equipment::where('account_id', $account->id)->first() ?? new Equipment;
        $equipment->account_id = $account->id;
        foreach (Equipment::PLUGIN_SLOTS as $column => $slot) {
            $itemId = $slots[$slot][0] ?? null;
            $equipment->{$column} = $itemId !== -1 ? $itemId : null;
        }
        $equipment->save();

        broadcast(new AccountDataUpdated($account, 'equipment'));

        return response()->json(['data' => $equipment]);
    }
}
