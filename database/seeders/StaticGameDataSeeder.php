<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;

class StaticGameDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Item::count() > 0) {
            $this->command->info(sprintf('static game data already populated (%d items); skipping', Item::count()));

            return;
        }

        if (env('LARAVEL_SAIL') === '1') {
            $this->command->error('static game data is empty and cannot be populated from inside the Laravel container.');
            $this->command->line('Run this on your HOST shell, then re-run db:seed:');
            $this->command->line('  ./vendor/bin/sail run --rm mongodb-static-populate');

            throw new \RuntimeException('static game data must be populated before seeding');
        }

        $this->command->info('Populating static game data (this can take a few minutes)...');

        $result = Process::path(base_path())
            ->timeout(900)
            ->run('docker compose run --rm mongodb-static-populate', function (string $type, string $buffer): void {
                $this->command->getOutput()->write($buffer);
            });

        if ($result->failed()) {
            throw new \RuntimeException('static game data populate container exited with status '.$result->exitCode());
        }

        $this->command->info(sprintf('static game data populated (%d items).', Item::count()));
    }
}
