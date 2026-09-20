<?php

namespace App\Console\Commands;

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class SyncResourcePackCss extends Command
{
    protected $signature = 'resourcepack:sync-css';

    protected $description = 'Re-copy the per-pack CSS template into every installed resource pack. Safe to run on every deploy.';

    /**
     * The per-pack stylesheet is copied into a pack when it is installed, so a
     * change to resources/css/per-pack.css otherwise reaches nothing that is
     * already on disk. Production makes that worse: public/resource-packs is
     * shared by symlink across releases, so installed packs survive a deploy
     * still carrying whatever CSS they were fetched with.
     *
     * Best-effort, like {@see EnsureVanillaResourcePack} — it warns rather than
     * failing so it can't abort a deploy.
     */
    public function handle(InstallResourcePack $installer): int
    {
        try {
            ['updated' => $updated, 'unchanged' => $unchanged, 'filled' => $filled] = $installer->syncTemplateCss();
        } catch (Throwable $e) {
            $this->warn('Could not sync per-pack CSS: '.$e->getMessage());

            return CommandAlias::SUCCESS;
        }

        foreach ($updated as $name) {
            $this->line("  refreshed {$name}");
        }

        $this->info(sprintf(
            'Per-pack CSS synced: %d refreshed, %d already current, %d sprite(s) borrowed from the default pack.',
            count($updated),
            count($unchanged),
            $filled,
        ));

        return CommandAlias::SUCCESS;
    }
}
