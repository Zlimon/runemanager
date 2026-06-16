<?php

namespace App\Services\Hiscores;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * SPEC §2.2 — validates a Group Ironman group name against the official OSRS
 * group page. The page responds 200 for both states; a missing group renders an
 * explicit "Unable to find group with name" notice, which is the only reliable
 * signal (there's no JSON endpoint).
 */
class GroupIronmanValidator
{
    public string $url;

    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->url = Config::get('services.osrs_group_ironman.url')
            ?: 'https://secure.runescape.com/m=hiscore_oldschool_ironman/group-ironman/view-group';

        $this->client = $client ?? new HttpClient;
    }

    /**
     * Whether a GIM group with this name exists. Returns null when the official
     * site can't be reached or answers unexpectedly, so callers can avoid
     * hard-blocking setup on a transient Jagex outage ("validated where possible").
     */
    public function exists(string $name): ?bool
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        try {
            $response = $this->client->request('GET', $this->url, [
                'query' => ['name' => $name],
                'timeout' => 8,
            ]);
        } catch (GuzzleException $e) {
            Log::warning('GIM group validation request failed', ['name' => $name, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return ! str_contains((string) $response->getBody(), 'Unable to find group with name');
    }
}
