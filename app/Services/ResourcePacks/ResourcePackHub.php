<?php

namespace App\Services\ResourcePacks;

use App\Models\ResourcePack;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Browse the RuneLite community resource-pack hub. Packs live as
 * branches (named `pack-*`) on the melkypie/resource-packs repo, each shipping
 * an icon.png; this lists them (cached) and flags which are already installed.
 */
class ResourcePackHub
{
    private const REPO = 'melkypie/resource-packs';

    private const CACHE_KEY = 'resource-pack-hub-branches';

    private const PROPERTIES_CACHE_PREFIX = 'resource-pack-hub-properties:';

    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->client = $client ?? new HttpClient;
    }

    /**
     * Available hub packs with a freshly-computed "installed" flag and, when
     * installed, the DB id so the admin can delete it.
     *
     * @return list<array{name: string, alias: string, icon_url: string, installed: bool, id: int|null}>
     */
    public function available(): array
    {
        $installed = ResourcePack::query()->pluck('id', 'name');

        return array_map(fn (string $name): array => [
            'name' => $name,
            'alias' => Str::title(str_replace(['pack-', '-'], ' ', $name)),
            'icon_url' => 'https://raw.githubusercontent.com/'.self::REPO.'/'.$name.'/icon.png',
            'installed' => $installed->has($name),
            'id' => $installed->get($name),
        ], $this->branches());
    }

    /**
     * A hub pack's own description — author, version and tags — read from its
     * pack.properties and cached, for describing a pack before it is installed.
     *
     * Deliberately separate from the installer's read of the same file.
     * InstallResourcePack::latestUpstreamVersion() drives resourcepack:update's
     * drift check, and a cached copy there would hide a real upstream release for
     * as long as it lived; this one only ever labels a dialog.
     *
     * Only names the hub actually lists are fetched. The name arrives from the
     * browser, and without that check any string would become a GitHub request
     * and a cache entry.
     *
     * A pack with no readable pack.properties is cached as empty, so a missing
     * file costs one request per cache lifetime rather than one per dialog. A
     * network failure is not cached, since it says nothing about the pack.
     *
     * @return array{author: ?string, version: ?string, tags: list<string>}|null
     */
    public function properties(string $name): ?array
    {
        if (! in_array($name, $this->branches(), true)) {
            return null;
        }

        $key = self::PROPERTIES_CACHE_PREFIX.$name;

        if (! Cache::has($key)) {
            try {
                $response = $this->client->get(
                    'https://raw.githubusercontent.com/'.self::REPO.'/'.$name.'/pack.properties',
                    ['http_errors' => false, 'timeout' => 10],
                );
            } catch (\Throwable $e) {
                Log::info('ResourcePackHub: properties fetch failed for '.$name.': '.$e->getMessage());

                return null;
            }

            $values = $response->getStatusCode() === 200
                ? self::parseProperties((string) $response->getBody())
                : [];

            Cache::put($key, $values, now()->addHours(6));
        }

        $values = Cache::get($key);

        if ($values === []) {
            return null;
        }

        return [
            'author' => ($values['author'] ?? '') ?: null,
            'version' => ($values['compatibleVersion'] ?? '') ?: null,
            'tags' => array_values(array_filter(array_map(
                'trim',
                explode(',', mb_strtolower($values['tags'] ?? '')),
            ))),
        ];
    }

    /**
     * Parse a pack.properties body into key => value pairs. The one parser for
     * that format, shared with the installer.
     *
     * @return array<string, string>
     */
    public static function parseProperties(string $body): array
    {
        $values = [];

        foreach (preg_split('/\r\n|\n|\r/', trim($body)) as $line) {
            if (! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value);
        }

        return $values;
    }

    /**
     * Cached list of `pack-*` branch names from the hub repo.
     *
     * @return list<string>
     */
    public function branches(): array
    {
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        try {
            $branches = $this->fetchBranches();
        } catch (\Throwable $e) {
            Log::info('ResourcePackHub: branch fetch failed: '.$e->getMessage());

            return [];
        }

        Cache::put(self::CACHE_KEY, $branches, now()->addHours(6));

        return $branches;
    }

    /**
     * @return list<string>
     */
    private function fetchBranches(): array
    {
        $names = [];

        for ($page = 1; $page <= 5; $page++) {
            $response = $this->client->request('GET', 'https://api.github.com/repos/'.self::REPO.'/branches', [
                'query' => ['per_page' => 100, 'page' => $page],
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'RuneManager',
                ],
                'timeout' => 15,
            ]);

            $rows = json_decode((string) $response->getBody(), true);
            if (! is_array($rows) || $rows === []) {
                break;
            }

            foreach ($rows as $row) {
                $name = $row['name'] ?? '';
                if (is_string($name) && str_starts_with($name, 'pack-')) {
                    $names[] = $name;
                }
            }

            if (count($rows) < 100) {
                break;
            }
        }

        sort($names);

        return $names;
    }
}
