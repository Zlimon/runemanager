<?php

namespace App\Console\Commands;

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class EnsureVanillaResourcePack extends Command
{
    protected $signature = 'resourcepack:ensure-vanilla';

    protected $description = 'Ensure the bundled Vanilla resource pack is installed (downloads it if the files are missing). Safe to run on every deploy.';

    /**
     * The Vanilla pack is the icon baseline used whenever no pack is
     * applied. Its files are gitignored and downloaded at runtime, so a fresh
     * environment (or a release before it was first installed) has none — this
     * restores them. Best-effort: it never fails so it can't abort a deploy.
     */
    public function handle(InstallResourcePack $installer): int
    {
        $name = InstallResourcePack::VANILLA_PACK;

        try {
            if ($installer->isInstalled($name)) {
                $installer->ensureVanilla();
                $this->info('Vanilla already installed.');
            } else {
                $installer->install($name);
                $this->info('Vanilla installed.');
            }
        } catch (Throwable $e) {
            $this->warn('Could not ensure Vanilla: '.$e->getMessage());
        }

        return CommandAlias::SUCCESS;
    }
}
