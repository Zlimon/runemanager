<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loot;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LootController extends Controller
{
    public function __construct(private RecordFeedEvent $feed) {}

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
            'loot.*.type' => ['nullable', 'string', 'max:20'],
            'loot.*.items' => ['required', 'array', 'min:1'],
            'loot.*.items.*.id' => ['required', 'integer'],
            'loot.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'loot.*.total_value' => ['nullable', 'integer', 'min:0'],
            'loot.*.killed_at' => ['required', 'date'],
        ]);

        $docs = [];
        $events = [];
        $now = now();

        foreach ($request->input('loot') as $entry) {
            $items = array_map(
                fn (array $item): array => [
                    'id' => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                ],
                $entry['items'],
            );
            $totalValue = (int) ($entry['total_value'] ?? 0);
            $killedAt = Carbon::parse($entry['killed_at']);

            $docs[] = [
                'account_id' => (int) $account->id,
                'source' => $entry['source'],
                'type' => $entry['type'] ?? null,
                'items' => $items,
                'total_value' => $totalValue,
                'killed_at' => $killedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $events[] = [
                'source' => $entry['source'],
                'items' => $items,
                'total_value' => $totalValue,
                'killed_at' => $killedAt,
            ];
        }

        Loot::insert($docs);

        // SPEC §8 — emit a LOOT_DROP for each entry above the configured floor.
        foreach ($events as $event) {
            $this->feed->recordLootDrop(
                $account,
                $event['source'],
                $event['items'],
                $event['total_value'],
                $event['killed_at'],
            );
        }

        return response()->json([
            'data' => ['inserted' => count($docs)],
        ], 201);
    }
}
