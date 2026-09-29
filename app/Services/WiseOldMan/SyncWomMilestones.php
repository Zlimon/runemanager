<?php

namespace App\Services\WiseOldMan;

use App\Models\WomAchievement;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use Illuminate\Support\Carbon;

/**
 * Store one account's Wise Old Man achievements and personal records, which
 * feed the Adventurer's Log and the Gains tab. `SyncWomPlayer` calls this only
 * when WOM saw a change since the last run, so an idle account costs nothing.
 * Both calls are GETs; a rate limit bubbles up to the job.
 */
class SyncWomMilestones
{
    public function __construct(private WiseOldManClient $client) {}

    /**
     * @throws WiseOldManRateLimited
     */
    public function sync(WomPlayer $player, string $username): void
    {
        $this->storeAchievements($player->account_id, $this->client->achievements($username));
        $this->storeRecords($player->account_id, $this->client->records($username));

        $player->milestones_synced_at = now();
        $player->save();
    }

    /**
     * Upsert by name and drop the rows WOM no longer returns (renamed or
     * retired achievements). An empty answer deletes nothing: it more likely
     * means WOM lost the player than that every achievement vanished.
     *
     * @param  array<int, array<string, mixed>>  $achievements
     */
    private function storeAchievements(int $accountId, array $achievements): void
    {
        $rows = collect($achievements)
            ->filter(fn ($row): bool => is_array($row) && isset($row['name'], $row['metric'], $row['measure']))
            ->map(fn (array $row): array => [
                'account_id' => $accountId,
                'name' => mb_substr((string) $row['name'], 0, 120),
                'metric' => (string) $row['metric'],
                'measure' => (string) $row['measure'],
                'threshold' => max(0, (int) ($row['threshold'] ?? 0)),
                'achieved_at' => self::achievedAt($row['createdAt'] ?? null),
                'accuracy' => isset($row['accuracy']) ? max(0, (int) $row['accuracy']) : null,
                'legacy' => (bool) ($row['legacy'] ?? false),
            ])
            ->keyBy('name')
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        WomAchievement::query()->upsert(
            $rows->all(),
            ['account_id', 'name'],
            ['metric', 'measure', 'threshold', 'achieved_at', 'accuracy', 'legacy'],
        );

        WomAchievement::query()
            ->where('account_id', $accountId)
            ->whereNotIn('name', $rows->pluck('name')->all())
            ->delete();
    }

    /**
     * Upsert the current best per period and metric; five-minute records are
     * not stored.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    private function storeRecords(int $accountId, array $records): void
    {
        $rows = collect($records)
            ->filter(fn ($row): bool => is_array($row)
                && in_array($row['period'] ?? null, WomRecord::PERIODS, true)
                && isset($row['metric'], $row['value'], $row['updatedAt']))
            ->map(fn (array $row): array => [
                'account_id' => $accountId,
                'period' => $row['period'],
                'metric' => (string) $row['metric'],
                'value' => (float) $row['value'],
                // To the minute: records beaten by one WOM update share a moment,
                // which the Adventurer's Log collapses into one event.
                'achieved_at' => Carbon::parse($row['updatedAt'])->utc()->startOfMinute(),
            ])
            ->keyBy(fn (array $row): string => $row['period'].':'.$row['metric'])
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        WomRecord::query()->upsert(
            $rows->all(),
            ['account_id', 'period', 'metric'],
            ['value', 'achieved_at'],
        );
    }

    /**
     * WOM dates an achievement it cannot place in time at the Unix epoch.
     */
    private static function achievedAt(mixed $createdAt): ?Carbon
    {
        if (! is_string($createdAt) || $createdAt === '') {
            return null;
        }

        $date = Carbon::parse($createdAt)->utc();

        return $date->year <= 1970 ? null : $date;
    }
}
