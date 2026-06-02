<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LootController extends Controller
{
    /**
     * Append-only loot push from the RuneLite plugin. Account resolved by the
     * plugin.account middleware. Accepts a batch (the plugin may flush more
     * than one drop event in a single push) and inserts one document per entry.
     *
     * Payload (SPEC §5.2 Loot):
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
     *         "killed_at": "2026-06-02T17:30:00Z"
     *       }
     *     ]
     *   }
     */
    public function store(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'loot' => ['required', 'array', 'min:1', 'max:100'],
            'loot.*.source' => ['required', 'string', 'max:120'],
            'loot.*.items' => ['required', 'array', 'min:1'],
            'loot.*.items.*.id' => ['required', 'integer'],
            'loot.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'loot.*.total_value' => ['nullable', 'integer', 'min:0'],
            'loot.*.killed_at' => ['required', 'date'],
        ]);

        $docs = [];
        $now = now();

        foreach ($request->input('loot') as $entry) {
            $docs[] = [
                'account_id' => (int) $account->id,
                'source' => $entry['source'],
                'items' => array_map(
                    fn (array $item): array => [
                        'id' => (int) $item['id'],
                        'quantity' => (int) $item['quantity'],
                    ],
                    $entry['items'],
                ),
                'total_value' => (int) ($entry['total_value'] ?? 0),
                'killed_at' => Carbon::parse($entry['killed_at']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Loot::insert($docs);

        return response()->json([
            'data' => ['inserted' => count($docs)],
        ], 201);
    }
}
