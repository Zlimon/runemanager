<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Loot\RecordLoot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LootController extends Controller
{
    /**
     * Append-only loot push from the RuneLite plugin. Account resolved by the
     * plugin.account middleware. Accepts a batch (the plugin may flush more
     * than one drop event in a single push) and inserts one document per entry.
     *
     * Payload:
     *
     *   {
     *     "loot": [
     *       {
     *         "source": "Abyssal demon",
     *         "items": [
     *           {"id": 4151, "quantity": 1},
     *           {"id": 995,  "quantity": 10000}
     *         ],
     *         "total_value": 1230000,        // optional; plugin-computed GE total
     *         "killed_at": "2026-06-02T17:30:00Z",
     *         "feed": true                   // optional; false = log only, no feed event
     *       }
     *     ]
     *   }
     */
    public function store(Request $request, RecordLoot $recordLoot): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'loot' => ['required', 'array', 'min:1', 'max:100'],
            'loot.*.source' => ['required', 'string', 'max:120'],
            'loot.*.type' => ['nullable', 'string', 'max:20'],
            'loot.*.items' => ['required', 'array', 'min:1'],
            'loot.*.items.*.id' => ['required', 'integer'],
            'loot.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'loot.*.total_value' => ['nullable', 'integer', 'min:0'],
            'loot.*.killed_at' => ['required', 'date'],
            'loot.*.feed' => ['nullable', 'boolean'],
            ...RecordFeedEvent::positionRules('loot.*.position'),
        ]);

        return response()->json([
            'data' => ['inserted' => $recordLoot->record($account, $request->input('loot'))],
        ], 201);
    }
}
