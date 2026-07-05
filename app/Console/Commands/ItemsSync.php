<?php

namespace App\Console\Commands;

use App\Services\Items\ItemsSync as ItemsSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('items:sync {--limit= : Stop after N new items (for testing)}')]
#[Description('Backfill the static item DB with items added to the game since the osrsbox dataset was frozen — names/icons from the RuneLite static cache, GE metadata from the OSRS Wiki.')]
class ItemsSync extends Command
{
    public function handle(ItemsSyncService $sync): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $result = $sync->sync(function (string $itemId, string $name, bool $hasIcon): void {
            $this->line(sprintf('  %s %s — %s', $hasIcon ? '✓' : '–', $itemId, $name));
        }, $limit);

        if ($result['missing'] === 0) {
            $this->info('Item DB is up to date.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Inserted %d of %d missing items (%d without an icon).',
            $result['inserted'],
            $result['missing'],
            $result['icons_missing'],
        ));

        return self::SUCCESS;
    }
}
