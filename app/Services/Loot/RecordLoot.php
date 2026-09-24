<?php

namespace App\Services\Loot;

use App\Events\AccountDataUpdated;
use App\Models\Account;
use App\Models\Loot;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Support\Carbon;

/**
 * Append a batch of plugin loot entries to the loot log, notify the account's
 * open pages, and emit a LOOT_DROP feed event for each feed-worthy entry.
 */
class RecordLoot
{
    public function __construct(private RecordFeedEvent $feed) {}

    /**
     * Inserts one document per entry and returns how many were inserted.
     *
     * @param  array<int, array{source: string, type?: ?string, items: array<int, array{id: int|string, quantity: int|string}>, total_value?: ?int, killed_at: string, feed?: ?bool, position?: ?array<string, mixed>}>  $entries
     */
    public function record(Account $account, array $entries): int
    {
        $docs = [];
        $events = [];
        $now = now();

        foreach ($entries as $entry) {
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
                'type' => LootLeaderboard::recordType($entry['type'] ?? null),
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
                'feed' => (bool) ($entry['feed'] ?? true),
                'position' => $entry['position'] ?? null,
            ];
        }

        Loot::insert($docs);

        broadcast(new AccountDataUpdated($account, 'loot'));

        // Emit a LOOT_DROP for each entry the plugin marked as
        // feed-worthy (its "Feed loot value" setting; absent = older plugin,
        // treat as worthy). The admin's minimum value only filters the Live
        // Feed display, so these all reach the account Activity feed.
        foreach ($events as $event) {
            if (! $event['feed']) {
                continue;
            }

            $this->feed->recordLootDrop(
                $account,
                $event['source'],
                $event['items'],
                $event['total_value'],
                $event['killed_at'],
                $event['position'],
            );
        }

        return count($docs);
    }
}
