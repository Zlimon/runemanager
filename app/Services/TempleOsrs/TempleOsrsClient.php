<?php

namespace App\Services\TempleOsrs;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Client for the TempleOSRS API — the source for collection log data (SPEC §5.2).
 */
class TempleOsrsClient
{
    protected HttpClient $client;

    protected string $url;

    public function __construct(?HttpClient $client = null)
    {
        $this->url = rtrim((string) (Config::get('services.templeosrs.url') ?: 'https://templeosrs.com/api'), '/');
        $this->client = $client ?? new HttpClient;
    }

    /**
     * Fetch a player's collection log. Returns the `data` payload, or null when
     * the player hasn't synced their log on TempleOSRS / the request fails.
     *
     * @return array<string, mixed>|null
     */
    public function collectionLog(string $username): ?array
    {
        try {
            $response = $this->client->request('GET', $this->url.'/collection-log/player_collection_log.php', [
                'query' => ['player' => $username, 'categories' => 'all'],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 25,
            ]);

            $json = json_decode((string) $response->getBody(), true);

            return is_array($json) && isset($json['data']) && is_array($json['data'])
                ? $json['data']
                : null;
        } catch (\Throwable $e) {
            Log::info('TempleOsrsClient: collection log fetch failed for '.$username.': '.$e->getMessage());

            return null;
        }
    }

    /**
     * Fetch the full collection log structure — every category's complete, ordered
     * item-id list, grouped (bosses/raids/clues/minigames/other). This is static
     * game data (changes only with OSRS updates), so callers should cache it. It
     * gives us per-category totals and the missing (un-obtained) items the
     * player endpoint omits.
     *
     * @return array<string, array<string, list<int>>>|null group => slug => ids
     */
    public function collectionLogStructure(): ?array
    {
        try {
            $response = $this->client->request('GET', $this->url.'/collection-log/categories.php', [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 25,
            ]);

            $json = json_decode((string) $response->getBody(), true);

            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::info('TempleOsrsClient: collection log structure fetch failed: '.$e->getMessage());

            return null;
        }
    }
}
