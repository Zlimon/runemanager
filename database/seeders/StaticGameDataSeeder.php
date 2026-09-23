<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Makes sure the read-only `mongodb-static` item catalogue is populated before
 * the rest of the seed runs. An empty catalogue degrades the demo (item icons
 * and names go missing) but never aborts it: `Item::randomItemId()` returns 0
 * and `randomItemIds()` returns an empty list, so the Mongo-backed factories
 * produce empty snapshots instead of throwing.
 */
class StaticGameDataSeeder extends Seeder
{
    public function run(): void
    {
        $count = $this->itemCount();

        if ($count === null) {
            $this->warnDegraded('the mongodb-static connection is unreachable');

            return;
        }

        if ($count > 0) {
            $this->command->info(sprintf('static game data already populated (%d items); skipping', $count));

            return;
        }

        if (config('runemanager.seeding.in_container')) {
            $this->warnDegraded('static game data is empty and cannot be populated from inside the Laravel container');

            return;
        }

        $this->command->info('Populating static game data (this can take a few minutes)...');

        $result = Process::path(base_path())
            ->timeout(900)
            ->run('docker compose run --rm mongodb-static-populate', function (string $type, string $buffer): void {
                $this->command->getOutput()->write($buffer);
            });

        if ($result->failed()) {
            $this->warnDegraded('the populate container exited with status '.$result->exitCode());

            return;
        }

        $this->command->info(sprintf('static game data populated (%d items).', (int) $this->itemCount()));
    }

    /**
     * The catalogue size, or null when the static Mongo connection is down.
     */
    private function itemCount(): ?int
    {
        try {
            return Item::count();
        } catch (Throwable) {
            return null;
        }
    }

    private function warnDegraded(string $reason): void
    {
        $this->command->warn(sprintf('StaticGameDataSeeder: %s.', $reason));
        $this->command->warn('  Seeding continues, but item icons and names will be missing.');
        $this->command->line('  Populate it from your HOST shell, then re-run db:seed:');
        $this->command->line('    ./vendor/bin/sail run --rm mongodb-static-populate');
    }
}
