<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLootRequest;
use App\Services\Loot\RecordLoot;
use Illuminate\Http\JsonResponse;

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
    public function store(StoreLootRequest $request, RecordLoot $recordLoot): JsonResponse
    {
        $account = $request->attributes->get('account');

        return response()->json([
            'data' => ['inserted' => $recordLoot->record($account, $request->validated('loot'))],
        ], 201);
    }
}
