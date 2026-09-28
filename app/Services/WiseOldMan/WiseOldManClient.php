<?php

namespace App\Services\WiseOldMan;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Client for the Wise Old Man v2 API (https://docs.wiseoldman.net).
 *
 * Every request first spends one unit of a per-minute budget kept under WOM's
 * own limit (20/min, or 100/min with an API key), so a job that makes several
 * calls cannot overrun it. A 404 means WOM does not track the player and
 * returns null; a 429 or a spent budget throws {@see WiseOldManRateLimited}.
 */
class WiseOldManClient
{
    /** Cache key of the request budget shared by every caller. */
    private const BUDGET_KEY = 'wiseoldman:requests';

    /** Seconds to wait for WOM; queued jobs can afford to be patient. */
    private int $timeout = 20;

    /**
     * A copy for a page render: gives up after 5 seconds, so a slow WOM
     * costs the viewer an empty panel rather than a hanging page.
     */
    public function interactive(): static
    {
        $client = clone $this;
        $client->timeout = 5;

        return $client;
    }

    /**
     * Requests allowed per minute: a little headroom under WOM's own limit.
     * Also backs the `wiseoldman` job rate limiter.
     */
    public static function perMinute(): int
    {
        return config('services.wiseoldman.api_key') ? 90 : 18;
    }

    /**
     * WOM's username normalisation: lower-case, runs of `-`, `_` and
     * whitespace collapsed to one space, trimmed.
     */
    public static function standardise(string $username): string
    {
        return trim((string) preg_replace('/[-_\s]+/', ' ', mb_strtolower($username)));
    }

    /**
     * A tracked player's details (PlayerDetails, with `latestSnapshot`), or
     * null when WOM does not know the name. Never creates a WOM profile.
     *
     * @return array<string, mixed>|null
     */
    public function player(string $username): ?array
    {
        return $this->json($this->request()->get($this->playerPath($username)));
    }

    /**
     * Ask WOM to re-fetch the player's hiscores. This creates the profile if
     * WOM has never seen the name, so callers must have the owner's consent.
     * Null when the name is not on the official hiscores.
     *
     * @return array<string, mixed>|null
     */
    public function update(string $username): ?array
    {
        $response = $this->request()->post($this->playerPath($username));

        // 400: the name could not be found on the official hiscores.
        if ($response->status() === 400) {
            return null;
        }

        return $this->json($response);
    }

    /**
     * One page of the player's snapshots within a period, newest first. WOM
     * caps `limit` at 200. Each snapshot is `{id, playerId, createdAt,
     * importedAt, data: {skills, bosses, activities, computed}}`; empty when
     * the player is unknown.
     *
     * @return list<array<string, mixed>>
     */
    public function snapshots(string $username, string $period, int $limit = 200, int $offset = 0): array
    {
        return $this->list($this->request()->get($this->playerPath($username).'/snapshots', [
            'period' => $period,
            'limit' => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Every achievement the player has reached: `[{name, metric, measure,
     * threshold, createdAt, accuracy, legacy}]`. A `createdAt` of 1970-01-01
     * means WOM does not know the date. Empty when the player is unknown.
     *
     * @return list<array<string, mixed>>
     */
    public function achievements(string $username): array
    {
        return $this->list($this->request()->get($this->playerPath($username).'/achievements'));
    }

    /**
     * The player's current best gain per (period, metric): `[{period, metric,
     * value, updatedAt}]`, periods `five_min|day|week|month|year`. Empty when
     * the player is unknown.
     *
     * @return list<array<string, mixed>>
     */
    public function records(string $username): array
    {
        return $this->list($this->request()->get($this->playerPath($username).'/records'));
    }

    private function request(): PendingRequest
    {
        if (RateLimiter::tooManyAttempts(self::BUDGET_KEY, self::perMinute())) {
            throw new WiseOldManRateLimited(max(1, RateLimiter::availableIn(self::BUDGET_KEY)));
        }

        RateLimiter::hit(self::BUDGET_KEY, 60);

        $apiKey = config('services.wiseoldman.api_key');

        return Http::baseUrl(rtrim((string) config('services.wiseoldman.url'), '/'))
            ->withUserAgent((string) config('services.wiseoldman.user_agent'))
            ->withHeaders($apiKey ? ['x-api-key' => $apiKey] : [])
            ->acceptJson()
            ->timeout($this->timeout);
    }

    private function playerPath(string $username): string
    {
        return 'players/'.rawurlencode(self::standardise($username));
    }

    /**
     * @return list<mixed>
     */
    private function list(Response $response): array
    {
        $json = $this->json($response);

        return is_array($json) ? array_values($json) : [];
    }

    /**
     * @return array<mixed>|null
     *
     * @throws WiseOldManRateLimited
     * @throws RequestException
     */
    private function json(Response $response): ?array
    {
        if ($response->status() === 404) {
            return null;
        }

        if ($response->status() === 429) {
            throw new WiseOldManRateLimited(max(1, (int) ($response->header('Retry-After') ?: 60)));
        }

        $response->throw();

        $json = $response->json();

        return is_array($json) ? $json : null;
    }
}
