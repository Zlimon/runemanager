<?php

namespace App\Services\Accounts;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * SPEC §5.2 — confirm a username is a Group Ironman account before it can join a
 * GROUP-mode instance. TempleOSRS exposes a per-player `GIM` flag via its
 * player_info endpoint, which works even when the player isn't online (so it can
 * gate admin pre-creation as well as plugin-time linking).
 *
 * Results are cached briefly to avoid hammering TempleOSRS when a rejected
 * account keeps pushing. Failures (network errors, untracked players) read as
 * "not a GIM" — validation fails closed.
 */
class GroupIronmanValidator
{
    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->client = $client ?? new HttpClient;
    }

    public function isGroupIronman(string $username): bool
    {
        $username = trim($username);
        if ($username === '') {
            return false;
        }

        return Cache::remember(
            'gim-validation:'.mb_strtolower($username),
            now()->addHour(),
            fn (): bool => $this->lookup($username),
        );
    }

    private function lookup(string $username): bool
    {
        $base = rtrim((string) (Config::get('services.templeosrs.url') ?: 'https://templeosrs.com/api'), '/');

        try {
            $response = $this->client->request('GET', $base.'/player_info.php', [
                'query' => ['player' => $username],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 10,
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return (int) data_get($data, 'data.GIM', 0) === 1;
        } catch (\Throwable $e) {
            Log::warning('GroupIronmanValidator: lookup failed for '.$username.': '.$e->getMessage());

            return false;
        }
    }
}
