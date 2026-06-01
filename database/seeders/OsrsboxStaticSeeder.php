<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;

class OsrsboxStaticSeeder extends Seeder
{
    public function run(): void
    {
        if (Item::count() > 0) {
            $this->command->info(sprintf('osrsbox static data already populated (%d items); skipping', Item::count()));

            return;
        }

        if (env('LARAVEL_SAIL') === '1') {
            $this->command->error('osrsbox static data is empty and cannot be populated from inside the Laravel container.');
            $this->command->line('Run this on your HOST shell, then re-run db:seed:');
            $this->command->line('  ./vendor/bin/sail --profile populate run --rm osrsbox-populate');

            throw new \RuntimeException('osrsbox static data must be populated before seeding');
        }

        $this->command->info('Populating osrsbox static data (this can take a few minutes)...');

        $result = Process::path(base_path())
            ->timeout(900)
            ->run('docker compose --profile populate run --rm osrsbox-populate', function (string $type, string $buffer): void {
                $this->command->getOutput()->write($buffer);
            });

        if ($result->failed()) {
            throw new \RuntimeException('osrsbox populate container exited with status '.$result->exitCode());
        }

        $this->command->info(sprintf('osrsbox static data populated (%d items).', Item::count()));
    }
}
