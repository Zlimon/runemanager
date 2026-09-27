<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * An account's Wise Old Man data as its profile shows it: the stored summary,
 * the daily history chart and what it gained over a period. Only the
 * per-skill/boss breakdown asks WOM live (cached for an hour); everything
 * else reads our own tables.
 */
class WomAccountView
{
    /** How long a live `/gained` breakdown is reused. */
    public const GAINED_CACHE_SECONDS = 3600;

    public function __construct(
        private WiseOldManClient $client,
        private WomSnapshotHistory $history,
    ) {}

    /**
     * The stored WOM row, or null when the account has never been looked up.
     * The owner's own choices (`tracking_opt_in`, `show_on_progress`) are only
     * included when `$viewer` owns the account.
     *
     * @return array{linked: bool, not_found: bool, status: ?string, type: ?string, build: ?string, exp: int, ehp: float, ehb: float, ttm: float, tt200m: float, combat_level: ?int, registered_at: ?string, synced_at: ?string, url: ?string, tracking_opt_in?: bool, show_on_progress?: bool}|null
     */
    public function summary(Account $account, ?User $viewer = null): ?array
    {
        $player = $account->womPlayer;

        if ($player === null) {
            return null;
        }

        $linked = $player->isLinked();

        return [
            'linked' => $linked,
            'not_found' => ! $linked && $player->not_found_at !== null,
            'status' => $player->status,
            'type' => $player->type,
            'build' => $player->build,
            'exp' => (int) $player->exp,
            'ehp' => (float) $player->ehp,
            'ehb' => (float) $player->ehb,
            'ttm' => (float) $player->ttm,
            'tt200m' => (float) $player->tt200m,
            'combat_level' => $player->combat_level,
            'registered_at' => $player->registered_at?->toIso8601String(),
            'synced_at' => $player->synced_at?->toIso8601String(),
            'url' => $linked ? self::profileUrl($account->username) : null,
            ...($viewer !== null && $viewer->id === $account->user_id ? [
                'tracking_opt_in' => $player->tracking_opt_in,
                'show_on_progress' => $player->show_on_progress,
            ] : []),
        ];
    }

    /** The public wiseoldman.net page for a username. */
    public static function profileUrl(string $username): string
    {
        return 'https://wiseoldman.net/players/'.rawurlencode(WiseOldManClient::standardise($username));
    }

    /**
     * Daily points over a range (`week`, `month`, `year`, `all`), oldest first,
     * thinned to {@see WomSnapshotHistory::MAX_POINTS}. Empty for an unlinked account.
     *
     * @return list<array{t: int, exp: int, ehp: float, ehb: float}>
     */
    public function series(Account $account, string $range): array
    {
        if (! $account->womPlayer?->isLinked()) {
            return [];
        }

        $points = [];
        foreach ($this->history->points([$account->id], $this->history->rangeStart($range)) as $row) {
            $points[] = [
                't' => $this->history->timestamp($row['date']),
                'exp' => $row['exp'],
                'ehp' => $row['ehp'],
                'ehb' => $row['ehb'],
            ];
        }

        return $this->history->downsample($points);
    }

    /**
     * What the account gained over a period (`day`, `week`, `month`, `year`).
     * The headline comes from our daily history; the skill and boss breakdown
     * from WOM live, empty when WOM is unreachable or rate-limited.
     *
     * @return array{headline: array{exp: int, ehp: float, ehb: float}, skills: list<array{metric: string, gained: int, level: int}>, bosses: list<array{metric: string, gained: int}>}
     */
    public function gains(Account $account, string $period): array
    {
        $period = array_key_exists($period, WomSnapshotHistory::PERIODS) ? $period : 'week';

        if (! $account->womPlayer?->isLinked()) {
            return ['headline' => ['exp' => 0, 'ehp' => 0.0, 'ehb' => 0.0], 'skills' => [], 'bosses' => []];
        }

        return [
            'headline' => $this->history->gained([$account->id], $period)[$account->id]
                ?? ['exp' => 0, 'ehp' => 0.0, 'ehb' => 0.0],
            ...$this->breakdown($account, $period),
        ];
    }

    /**
     * @return array{skills: list<array{metric: string, gained: int, level: int}>, bosses: list<array{metric: string, gained: int}>}
     */
    private function breakdown(Account $account, string $period): array
    {
        try {
            return Cache::remember(
                "wom:gained:{$account->id}:{$period}",
                self::GAINED_CACHE_SECONDS,
                fn (): array => $this->parseGained($this->client->interactive()->gained($account->username, $period)),
            );
        } catch (WiseOldManRateLimited|ConnectionException|RequestException) {
            return ['skills' => [], 'bosses' => []];
        } catch (Throwable $e) {
            // Anything else (a malformed payload, say) must not break the
            // profile page either, but is worth knowing about.
            report($e);

            return ['skills' => [], 'bosses' => []];
        }
    }

    /**
     * @param  array<string, mixed>|null  $gained
     * @return array{skills: list<array{metric: string, gained: int, level: int}>, bosses: list<array{metric: string, gained: int}>}
     */
    private function parseGained(?array $gained): array
    {
        $skills = collect($gained['data']['skills'] ?? [])
            ->reject(fn (array $skill, string $metric): bool => $metric === 'overall')
            ->map(fn (array $skill, string $metric): array => [
                'metric' => $metric,
                'gained' => (int) ($skill['experience']['gained'] ?? 0),
                'level' => (int) ($skill['level']['end'] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['gained'] > 0)
            ->sortByDesc('gained')
            ->values()
            ->all();

        $bosses = collect($gained['data']['bosses'] ?? [])
            ->map(fn (array $boss, string $metric): array => [
                'metric' => $metric,
                'gained' => (int) ($boss['kills']['gained'] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['gained'] > 0)
            ->sortByDesc('gained')
            ->values()
            ->all();

        return ['skills' => $skills, 'bosses' => $bosses];
    }
}
