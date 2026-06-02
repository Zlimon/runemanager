<?php

namespace App\Console\Commands;

use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('resourcepack:update {name? : Only check this pack; otherwise all installed packs}')]
#[Description('Check installed resource packs for upstream version drift and queue re-fetches on mismatch.')]
class ResourcePackUpdate extends Command
{
    public function handle(InstallResourcePack $installer): int
    {
        $packs = $this->argument('name')
            ? ResourcePack::where('name', $this->argument('name'))->get()
            : ResourcePack::all();

        if ($packs->isEmpty()) {
            $this->info('No resource packs to check.');

            return self::SUCCESS;
        }

        $checked = 0;
        $stale = 0;

        foreach ($packs as $pack) {
            $checked++;

            $latest = $installer->latestUpstreamVersion($pack->name);

            if ($latest === null) {
                $this->line(sprintf('  ? %s — upstream unreachable, skipping', $pack->name));

                continue;
            }

            if ($latest === $pack->version) {
                $this->line(sprintf('  ✓ %s — up to date (%s)', $pack->name, $pack->version));

                continue;
            }

            $this->line(sprintf('  ↻ %s — stale (%s → %s), queued', $pack->name, $pack->version, $latest));
            FetchResourcePackJob::dispatch($pack->name);
            $stale++;
        }

        $this->info(sprintf('Checked %d, queued %d for re-install.', $checked, $stale));

        return self::SUCCESS;
    }
}
