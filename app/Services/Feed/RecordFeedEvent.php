<?php

namespace App\Services\Feed;

use App\Events\FeedEventCreated;
use App\Models\Account;
use App\Models\FeedEvent;
use Illuminate\Support\Carbon;

/**
 * Single entry point for SPEC §8 live feed event generation.
 *
 * Each push controller/service that produces qualifying events instantiates
 * this and calls the matching record*() helper. Every helper takes an optional
 * plugin-supplied position ({x, y, plane, location}) that is normalised into
 * the payload so the feed can show where the event happened.
 */
class RecordFeedEvent
{
    /** Plugin quest status code for a finished quest (0/1/2 scale). */
    private const QUEST_FINISHED = 2;

    /**
     * Validation rules for an optional plugin-supplied event position, keyed
     * under $attribute so controllers can splice them into their own rules.
     *
     * @return array<string, array<int, string>>
     */
    public static function positionRules(string $attribute = 'position'): array
    {
        return [
            $attribute => ['nullable', 'array'],
            $attribute.'.x' => ['required_with:'.$attribute, 'integer'],
            $attribute.'.y' => ['required_with:'.$attribute, 'integer'],
            $attribute.'.plane' => ['nullable', 'integer', 'between:0,3'],
            $attribute.'.location' => ['nullable', 'string', 'max:60'],
        ];
    }

    /**
     * Record a LOOT_DROP event. The plugin decides which drops are feed-worthy
     * (its "Feed loot value" setting flags each entry); the admin's minimum
     * loot value is a Live Feed display filter, not a storage filter, so the
     * account Activity feed can show everything the plugin posted.
     *
     * @param  array<int, array{id: int, quantity: int}>  $items
     * @param  ?array<string, mixed>  $position
     */
    public function recordLootDrop(
        Account $account,
        string $source,
        array $items,
        int $totalValue,
        Carbon $occurredAt,
        ?array $position = null,
    ): FeedEvent {
        return $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_LOOT_DROP,
            'payload' => $this->withPosition([
                'source' => $source,
                'items' => $items,
                'total_value' => $totalValue,
            ], $position),
            'occurred_at' => $occurredAt,
        ]);
    }

    /**
     * Compare two quest snapshots and record a QUEST_COMPLETE for each quest
     * that flipped to "finished" (plugin status code 2 on the 0/1/2 scale).
     *
     * @param  array<int, array{0: string, 1: int}>  $previous
     * @param  array<int, array{0: string, 1: int}>  $current
     * @param  ?array<string, mixed>  $position
     */
    public function recordQuestCompletions(
        Account $account,
        array $previous,
        array $current,
        ?array $position = null,
    ): int {
        $prevStatus = [];
        foreach ($previous as [$name, $status]) {
            $prevStatus[$name] = $status;
        }

        $emitted = 0;
        $now = now();

        foreach ($current as [$name, $status]) {
            $wasFinished = ($prevStatus[$name] ?? null) === self::QUEST_FINISHED;
            $isFinished = $status === self::QUEST_FINISHED;

            if ($isFinished && ! $wasFinished) {
                $this->emit([
                    'account_id' => $account->id,
                    'type' => FeedEvent::TYPE_QUEST_COMPLETE,
                    'payload' => $this->withPosition(['quest' => $name], $position),
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
     * @param  ?array<string, mixed>  $position
     */
    public function record(Account $account, string $type, array $payload = [], ?array $position = null): FeedEvent
    {
        return $this->emit([
            'account_id' => $account->id,
            'type' => $type,
            'payload' => $this->withPosition($payload, $position),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Record a COMBAT_ACHIEVEMENT unlock (SPEC §8.1). Unlike level-ups/quests
     * these aren't derived from a snapshot diff — the plugin parses the in-game
     * completion message and pushes the task name + tier directly.
     *
     * @param  ?array<string, mixed>  $position
     */
    public function recordCombatAchievement(
        Account $account,
        string $task,
        ?string $tier = null,
        ?array $position = null,
    ): FeedEvent {
        return $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_COMBAT_ACHIEVEMENT,
            'payload' => $this->withPosition(['task' => $task, 'tier' => $tier], $position),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Record a COLLECTION_LOG slot unlock (SPEC §8.1). Like combat achievements,
     * the plugin parses the in-game "new item" notice and pushes the item name.
     *
     * @param  ?array<string, mixed>  $position
     */
    public function recordCollectionLogSlot(Account $account, string $item, ?array $position = null): FeedEvent
    {
        return $this->emit([
            'account_id' => $account->id,
            'type' => FeedEvent::TYPE_COLLECTION_LOG,
            'payload' => $this->withPosition(['item' => $item], $position),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Merge a normalised position into an event payload. Skipped entirely when
     * the plugin sent none (or an unusable one), so old events and new events
     * without coordinates look identical.
     *
     * @param  array<string, mixed>  $payload
     * @param  ?array<string, mixed>  $position
     * @return array<string, mixed>
     */
    private function withPosition(array $payload, ?array $position): array
    {
        if ($position === null || ! isset($position['x'], $position['y'])) {
            return $payload;
        }

        $location = $position['location'] ?? null;

        $payload['position'] = [
            'x' => (int) $position['x'],
            'y' => (int) $position['y'],
            'plane' => (int) ($position['plane'] ?? 0),
            'location' => $location !== null && $location !== '' ? (string) $location : null,
        ];

        return $payload;
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
