<?php

namespace App\Console\Commands;

use App\Models\AccountHiscore;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

#[Signature('boss-icons:fetch {--force : Overwrite existing icons} {--limit= : Stop after N downloads (for testing)}')]
#[Description('Refresh /public/images/boss/{slug}.png from the OSRS Wiki. Tries {Name}_icon.png (native 25×25) first; falls back to {Name}.png resized to 32×32.')]
class FetchBossIcons extends Command
{
    /**
     * Boss slugs whose wiki page name doesn't match the naive Title-Case
     * transformation (e.g. apostrophes, dashes inside capitalised words).
     * If the value is null the entry is treated as "skip this slug entirely"
     * — used for the hiscores' Bounty Hunter Legacy duplicates etc.
     *
     * @var array<string, ?string>
     */
    private const WIKI_NAME_OVERRIDES = [
        'tzkal_zuk' => 'TzKal-Zuk',
        'tztok_jad' => 'TzTok-Jad',
        'kril_tsutsaroth' => "K'ril_Tsutsaroth",
        'kreearra' => "Kree'arra",
        'vetion' => "Vet'ion",
        'calvarion' => "Calvar'ion",
        'phosanis_nightmare' => "Phosani's_Nightmare",
        'chambers_of_xeric_challenge_mode' => 'Chambers_of_Xeric/Challenge_Mode',
        'theatre_of_blood_hard_mode' => 'Theatre_of_Blood/Hard_Mode',
        'tombs_of_amascut_expert_mode' => 'Tombs_of_Amascut/Expert_Mode',
        // Bounty Hunter Legacy slugs roll into the Bounty Hunter category — same icon.
        'bounty_hunter_legacy_rogue' => 'Bounty_Hunter_(Hunter)',
        'bounty_hunter_legacy_hunter' => 'Bounty_Hunter_(Hunter)',
    ];

    public function handle(): int
    {
        $bosses = $this->bossSlugs();

        if ($bosses === []) {
            $this->error('No bosses found in any AccountHiscore row — sync at least one account first.');

            return self::FAILURE;
        }

        $this->info(sprintf('Fetching %d boss icons from the OSRS Wiki...', count($bosses)));

        $iconDir = public_path('images/boss');
        if (! is_dir($iconDir)) {
            mkdir($iconDir, 0o755, true);
        }

        $force = (bool) $this->option('force');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $downloaded = 0;
        $skipped = 0;
        $failed = [];

        foreach ($bosses as $slug) {
            if ($limit !== null && $downloaded >= $limit) {
                break;
            }

            $filename = str_replace('_', '-', $slug).'.png';
            $target = $iconDir.'/'.$filename;

            if (! $force && file_exists($target)) {
                $skipped++;

                continue;
            }

            $wikiName = $this->wikiName($slug);
            if ($wikiName === null) {
                $this->line(sprintf('  - %s (skipped — no wiki mapping)', $slug));
                $skipped++;

                continue;
            }

            $bytes = $this->downloadIcon($wikiName);
            if ($bytes === null) {
                $failed[] = $slug;
                $this->line(sprintf('  ✗ %s (%s)', $slug, $wikiName));

                // Back off when the wiki starts shedding load.
                usleep(500_000);

                continue;
            }

            file_put_contents($target, $bytes);
            $downloaded++;
            $this->line(sprintf('  ✓ %s (%d bytes)', $slug, strlen($bytes)));

            // Be polite — wiki started returning empty bodies around request 36.
            usleep(300_000);
        }

        $this->info(sprintf('Done — %d downloaded, %d skipped, %d failed.', $downloaded, $skipped, count($failed)));

        if ($failed !== []) {
            $this->newLine();
            $this->warn('Failed slugs (will fall back to /images/boss/boss.png in the UI):');
            foreach ($failed as $f) {
                $this->line('  - '.$f);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Union of every distinct activity slug across all AccountHiscore rows that
     * is shaped like a boss — drops clue tiers and the misc activity list that
     * Account::getBossesAttribute() also rejects.
     *
     * @return list<string>
     */
    private function bossSlugs(): array
    {
        $skipPrefixes = ['clue_scrolls_'];
        $skipExact = [
            'lms_rank', 'pvp_arena_rank', 'soul_wars_zeal', 'rifts_closed',
            'colosseum_glory', 'collections_logged', 'league_points', 'grid_points',
            'deadman_points', 'lunar_chests', 'barrows_chests',
            'bounty_hunter_hunter', 'bounty_hunter_rogue',
        ];

        $slugs = [];
        AccountHiscore::query()->select('entries')->cursor()->each(function ($row) use (&$slugs, $skipPrefixes, $skipExact): void {
            foreach (array_keys($row->entries['activities'] ?? []) as $slug) {
                foreach ($skipPrefixes as $prefix) {
                    if (str_starts_with($slug, $prefix)) {
                        continue 2;
                    }
                }
                if (in_array($slug, $skipExact, true)) {
                    continue;
                }
                $slugs[$slug] = true;
            }
        });

        return array_keys($slugs);
    }

    private function wikiName(string $slug): ?string
    {
        if (array_key_exists($slug, self::WIKI_NAME_OVERRIDES)) {
            return self::WIKI_NAME_OVERRIDES[$slug];
        }

        return str_replace(' ', '_', Str::title(str_replace('_', ' ', $slug)));
    }

    /**
     * Try the wiki's {Name}_icon.png (small native 25×25 icon) first; fall
     * back to the full {Name}.png resized to 32×32 for newer bosses without
     * a dedicated icon variant. Returns the PNG bytes or null on failure.
     */
    private function downloadIcon(string $wikiName): ?string
    {
        $iconUrl = sprintf(
            'https://oldschool.runescape.wiki/w/Special:FilePath/%s_icon.png',
            rawurlencode($wikiName),
        );
        $bytes = $this->httpGet($iconUrl);
        if ($bytes !== null && $this->isPng($bytes)) {
            return $bytes;
        }

        $fullUrl = sprintf(
            'https://oldschool.runescape.wiki/w/Special:FilePath/%s.png',
            rawurlencode($wikiName),
        );
        $bytes = $this->httpGet($fullUrl);
        if ($bytes === null || ! $this->isPng($bytes)) {
            return null;
        }

        return $this->resize($bytes, 32, 32) ?? $bytes;
    }

    /**
     * Shell out to curl rather than Guzzle — the wiki returns a 629KB anti-bot
     * HTML page to Guzzle's default request shape even after setting a friendly
     * User-Agent, but accepts curl's request identically. Probably a TLS or
     * header-ordering quirk on the wiki's CDN side. Curl is universally
     * available in the sail image so this isn't a portability concern.
     *
     * Body capped at 2MB to keep a misbehaving response from blowing memory.
     */
    private function httpGet(string $url): ?string
    {
        $result = Process::timeout(20)->run([
            'curl', '-sSL', '--max-time', '20',
            '-A', 'RuneManager-IconFetcher/1.0 (boss-icons:fetch artisan command)',
            '--max-filesize', '2000000',
            $url,
        ]);

        if (! $result->successful()) {
            return null;
        }

        $body = $result->output();

        return $body !== '' ? $body : null;
    }

    private function isPng(string $bytes): bool
    {
        return str_starts_with($bytes, "\x89PNG\r\n\x1a\n");
    }

    private function resize(string $pngBytes, int $w, int $h): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }
        $src = @imagecreatefromstring($pngBytes);
        if ($src === false) {
            return null;
        }
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $srcW, $srcH);

        ob_start();
        imagepng($dst);
        $out = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $out !== false && $out !== '' ? $out : null;
    }
}
