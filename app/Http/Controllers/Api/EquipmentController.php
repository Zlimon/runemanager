<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     *
     * The plugin sends `equipment` as a sparse array indexed by OSRS KitType slot. Each
     * present slot is a [itemId, quantity] pair; -1 in position 0 means an empty slot.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'equipment' => ['required', 'array'],
            'equipment.*' => ['required', 'array', 'min:1'],
            'equipment.*.0' => ['required', 'integer'],
        ]);

        $eq = $request->input('equipment');
        $itemAt = fn (int $slot) => isset($eq[$slot][0]) && $eq[$slot][0] !== -1 ? $eq[$slot][0] : null;

        $equipment = Equipment::where('account_id', $account->id)->first() ?? new Equipment;
        $equipment->account_id = $account->id;
        $equipment->head = $itemAt(0);
        $equipment->cape = $itemAt(1);
        $equipment->neck = $itemAt(2);
        $equipment->weapon = $itemAt(3);
        $equipment->body = $itemAt(4);
        $equipment->shield = $itemAt(5);
        $equipment->legs = $itemAt(7);
        $equipment->hands = $itemAt(9);
        $equipment->feet = $itemAt(10);
        $equipment->ring = $itemAt(12);
        $equipment->ammo = $itemAt(13);
        $equipment->save();

        return response()->json(['data' => $equipment]);
    }
}
