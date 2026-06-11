<?php

namespace App\Services\ResourcePacks;

use App\Models\ResourcePack;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SPEC §6 — browse the RuneLite community resource-pack hub. Packs live as
 * branches (named `pack-*`) on the melkypie/resource-packs repo, each shipping
 * an icon.png; this lists them (cached) and flags which are already installed.
 */
class ResourcePackHub
{
    private const REPO = 'melkypie/resource-packs';

    private const CACHE_KEY = 'resource-pack-hub-branches';

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
