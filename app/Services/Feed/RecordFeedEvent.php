<?php

namespace App\Services\Feed;

use App\Models\Account;
use App\Models\FeedEvent;
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
     * Compare two skill snapshots and record a LEVEL_UP for each notable
     * threshold crossed. Returns the number of events created.
     *
     * @param  array<string, array<string, int>>  $previous  entries['skills'] before sync
     * @param  array<string, array<string, int>>  $current  entries['skills'] after sync
     */
    public function recordLevelUps(Account $account, array $previous, array $current): int
    {
        /** @var int[] $thresholds */
        $thresholds = config('runemanager.feed.level_up_thresholds', []);
        sort($thresholds);

        $emitted = 0;
        $now = now();

        foreach ($current as $slug => $entry) {
            if ($slug === 'overall') {
                continue;
            }

            $newLevel = (int) ($entry['level'] ?? 1);
            $oldLevel = (int) ($previous[$slug]['level'] ?? 1);

            if ($newLevel <= $oldLevel) {
                continue;
            }

            $crossed = array_values(array_filter(
                $thresholds,
                fn (int $t) => $t > $oldLevel && $t <= $newLevel,
            ));

            if ($crossed === []) {
                continue;
            }

            // Use the highest threshold crossed in one tick — going 88 → 99
            // shouldn't emit four events.
            $milestone = end($crossed);

            FeedEvent::create([
                'account_id' => $account->id,
                'type' => FeedEvent::TYPE_LEVEL_UP,
                'payload' => [
                    'skill' => $slug,
                    'level' => $newLevel,
                    'milestone' => $milestone,
                ],
                'occurred_at' => $now,
            ]);
            $emitted++;
        }

        return $emitted;
    }

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
        $floor = (int) config('runemanager.feed.loot_min_value', 0);
        if ($totalValue < $floor) {
            return false;
        }

        FeedEvent::create([
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
                FeedEvent::create([
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
}
