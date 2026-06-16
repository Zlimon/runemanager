<?php

namespace App\Services\Feed;

use App\Events\FeedEventCreated;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Support\Instance;
use Illuminate\Support\Carbon;

/**
 * Single entry point for SPEC §8 live feed event generation.
 *
 * Each push controller/service that produces qualifying events instantiates
 * this and calls the matching record*() helper; centralising the filtering
 * logic (level thresholds, loot value floor) keeps the rules in one place
 * once the admin backend (§12) lets these be tuned at runtime.
 */
class RecordFeedEvent
{
    /**
     * Record a LOOT_DROP event when an inbound loot entry clears the configured
     * value floor. Returns true if an event was recorded.
     *
     * @param  array<int, array{id: int, quantity: int}>  $items
     */
    public function recordLootDrop(
        Account $account,
        string $source,
        array $items,
        int $totalValue,
        Carbon $occurredAt,
    ): bool {
        $floor = Instance::feedLootMinValue();
        if ($totalValue < $floor) {
            return false;
        }

        $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_LOOT_DROP,
            'payload' => [
                'source' => $source,
                'items' => $items,
                'total_value' => $totalValue,
            ],
            'occurred_at' => $occurredAt,
        ]);

        return true;
    }

    /**
     * Compare two quest snapshots and record a QUEST_COMPLETE for each quest
     * that flipped to "finished" (status 901389 — the OSRS sentinel for done).
     *
     * @param  array<int, array{0: string, 1: int}>  $previous
     * @param  array<int, array{0: string, 1: int}>  $current
     */
    public function recordQuestCompletions(Account $account, array $previous, array $current): int
    {
        $prevStatus = [];
        foreach ($previous as [$name, $status]) {
            $prevStatus[$name] = $status;
        }

        $emitted = 0;
        $now = now();

        foreach ($current as [$name, $status]) {
            $wasFinished = ($prevStatus[$name] ?? null) === 901389;
            $isFinished = $status === 901389;

            if ($isFinished && ! $wasFinished) {
                $this->emit([
                    'account_id' => $account->id,
                    'type' => FeedEvent::TYPE_QUEST_COMPLETE,
                    'payload' => ['quest' => $name],
                    'occurred_at' => $now,
                ]);
                $emitted++;
            }
        }

        return $emitted;
    }

    /**
     * Record a simple plugin-detected event (pet / death / reward) — the plugin
     * spots these in-game (mirroring the official Screenshot plugin) and pushes
     * the type + a small payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function record(Account $account, string $type, array $payload = []): FeedEvent
    {
        return $this->emit([
            'account_id' => $account->id,
            'type' => $type,
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Record a COMBAT_ACHIEVEMENT unlock (SPEC §8.1). Unlike level-ups/quests
     * these aren't derived from a snapshot diff — the plugin parses the in-game
     * completion message and pushes the task name + tier directly.
     */
    public function recordCombatAchievement(Account $account, string $task, ?string $tier = null): FeedEvent
    {
        return $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_COMBAT_ACHIEVEMENT,
            'payload' => ['task' => $task, 'tier' => $tier],
            'occurred_at' => now(),
        ]);
    }

    /**
     * Record a COLLECTION_LOG slot unlock (SPEC §8.1). Like combat achievements,
     * the plugin parses the in-game "new item" notice and pushes the item name.
     */
    public function recordCollectionLogSlot(Account $account, string $item): FeedEvent
    {
        return $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_COLLECTION_LOG,
            'payload' => ['item' => $item],
            'occurred_at' => now(),
        ]);
    }

    /**
     * Persist a feed event and broadcast it to connected browsers (SPEC §8.3).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function emit(array $attributes): FeedEvent
    {
        $event = FeedEvent::create($attributes);

        broadcast(new FeedEventCreated($event));

        return $event;
    }
}
