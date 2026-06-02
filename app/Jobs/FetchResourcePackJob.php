<?php

namespace App\Jobs;

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchResourcePackJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public string $packName) {}

    /**
     * The job is idempotent — re-runs hit the same upstream URL and overwrite
     * the same on-disk extract. Safe to retry.
     */
    public function handle(InstallResourcePack $installer): void
    {
        try {
            $installer->install($this->packName);
        } catch (Throwable $e) {
            Log::warning('FetchResourcePackJob failed', [
                'pack' => $this->packName,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw so the queue tracks the failure / retries with backoff.
            throw $e;
        }
    }
}
