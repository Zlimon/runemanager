<?php

namespace App\Services\ResourcePacks;

use App\Models\ResourcePack;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Download + extract a RuneLite resource pack into its own per-pack directory and
 * register/refresh the matching {@see ResourcePack} row.
 *
 * The pack ZIPs come from the {@code melkypie/resource-packs} GitHub repo where
 * each pack is a branch and the archive URL is
 * {@code https://github.com/melkypie/resource-packs/archive/{name}.zip}.
 *
 * Disk layout (per pack):
 *   {system tmp}/rpz_*.zip                          ← downloaded archive (deleted after extract)
 *   public/resource-packs/{name}/                   ← extracted, served as static assets
 *   public/resource-packs/{name}/resource-pack.css  ← copied from resources/css/per-pack.css
 *
 * The ZIP and the extraction scratch space live in the system temp dir rather
 * than under storage/, so a stale-perms staging directory can't permanently
 * block installs — temp is always writable by the running user.
 */
class InstallResourcePack
{
    /**
     * @return ResourcePack the upserted row
     *
     * @throws RuntimeException on download / extract failure
     */
    public function install(string $name): ResourcePack
    {
        File::ensureDirectoryExists(public_path('resource-packs'));

        $zipPath = tempnam(sys_get_temp_dir(), 'rpz_');
        if ($zipPath === false) {
            throw new RuntimeException('Could not allocate temp file for pack download');
        }
        $extractTarget = public_path("resource-packs/{$name}");

        try {
            $this->download($name, $zipPath);
            $this->extract($zipPath, $extractTarget);
            $this->copyTemplateCss($extractTarget);
        } finally {
            @unlink($zipPath);
        }

        return $this->upsertRow($name);
    }

    public function isInstalled(string $name): bool
    {
        return File::exists(public_path("resource-packs/{$name}/resource-pack.css"));
    }

    /**
     * Fetch the {@code compatibleVersion} from the pack's upstream {@code pack.properties}.
     * Returns null if upstream is unreachable or the properties file is missing — callers
     * should treat that as "no information; don't change anything".
     */
    public function latestUpstreamVersion(string $name): ?string
    {
        $properties = $this->fetchProperties($name);

        return $properties['compatibleVersion'] ?? null;
    }

    private function download(string $name, string $zipPath): void
    {
        $url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);

        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->get($url);

        if (! $response->ok()) {
            throw new RuntimeException(sprintf(
                'Failed to fetch resource pack "%s" from %s — HTTP %d',
                $name, $url, $response->status(),
            ));
        }

        File::put($zipPath, $response->body());
    }

    private function extract(string $zipPath, string $extractTarget): void
    {
        // Clean any prior extract for this pack so leftover files from a previous
        // version don't linger.
        if (File::exists($extractTarget)) {
            File::deleteDirectory($extractTarget);
        }
        File::ensureDirectoryExists($extractTarget);

        $tmpDir = sys_get_temp_dir().'/rpx_'.Str::random(8);
        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($tmpDir);
            throw new RuntimeException(sprintf('Could not open %s', $zipPath));
        }

        try {
            if (! $zip->extractTo($tmpDir)) {
                throw new RuntimeException(sprintf('Could not extract %s to %s', $zipPath, $tmpDir));
            }
        } finally {
            $zip->close();
        }

        // GitHub archive ZIPs nest everything under a single top-level dir
        // ({repo}-{branch}/). Move that dir's contents up one level.
        $entries = array_values(array_diff(scandir($tmpDir), ['.', '..']));
        if (count($entries) === 1 && is_dir($tmpDir.'/'.$entries[0])) {
            $inner = $tmpDir.'/'.$entries[0];
            File::copyDirectory($inner, $extractTarget);
        } else {
            File::copyDirectory($tmpDir, $extractTarget);
        }

        File::deleteDirectory($tmpDir);
    }

    private function copyTemplateCss(string $extractTarget): void
    {
        $template = resource_path('css/per-pack.css');
        if (! File::exists($template)) {
            throw new RuntimeException('Per-pack CSS template missing at '.$template);
        }
        File::copy($template, $extractTarget.'/resource-pack.css');
    }

    private function upsertRow(string $name): ResourcePack
    {
        $properties = $this->fetchProperties($name);
        $tags = $properties ? array_map('trim', explode(',', $properties['tags'] ?? '')) : [];
        $tags = array_map(fn ($t) => Str::lower($t), array_filter($tags));

        // ResourcePack has no $fillable — set attributes directly to avoid mass-assignment guards.
        $pack = ResourcePack::where('name', $name)->first() ?? new ResourcePack;
        $pack->name = $name;
        $pack->alias = $properties['displayName'] ?? Str::title(str_replace(['pack-', '-'], ' ', $name));
        $pack->version = $properties['compatibleVersion'] ?? '1.0.0';
        $pack->author = $properties['author'] ?? 'unknown';
        $pack->url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);
        $pack->tags = implode(',', $tags);
        $pack->dark_mode = in_array('dark', $tags, true);
        $pack->save();

        return $pack;
    }

    /**
     * @return array<string, string>|null
     */
    private function fetchProperties(string $name): ?array
    {
        $url = sprintf('https://raw.githubusercontent.com/melkypie/resource-packs/%s/pack.properties', $name);
        $response = Http::timeout(20)->get($url);
        if (! $response->ok()) {
            return null;
        }

        $values = [];
        foreach (preg_split('/\r\n|\n|\r/', trim($response->body())) as $line) {
            if (! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value);
        }

        return $values ?: null;
    }
}
