<?php

namespace App\Services\Instance;

use App\Models\AccountHiscore;
use App\Models\CollectionLog;
use App\Services\Screenshots\ScreenshotStorage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Operational health for the admin dashboard: queue backlog, background sync
 * freshness and on-disk storage usage. Every reading is best-effort — a
 * missing table, an unreachable disk or a misconfigured queue connection
 * degrades that one reading to null/0 rather than failing the whole panel.
 */
class SystemHealth
{
    /**
     * Storage usage walks every file under the screenshots and resource-pack
     * directories (thousands of sprites in production, an ever-growing
     * screenshot dir), so it's cached rather than recomputed on every
     * dashboard view. Everything else in report() stays live.
     */
    private const STORAGE_CACHE_SECONDS = 600;

    /**
     * @return array{
     *     failed_jobs: array{count: int, latest: list<array{id: int, uuid: string, queue: string, message: ?string, failed_at: ?string}>},
     *     pending_jobs: array{count: ?int, driver: string},
     *     sync: array{hiscores_last_synced_at: ?string, collection_log_last_synced_at: ?string},
     *     storage: array{screenshots_bytes: ?int, resource_packs_bytes: ?int},
     * }
     */
    public function report(): array
    {
        return [
            'failed_jobs' => $this->failedJobs(),
            'pending_jobs' => $this->pendingJobs(),
            'sync' => $this->syncFreshness(),
            'storage' => $this->storageUsage(),
        ];
    }

    /**
     * `uuid` is what Admin\FailedJobController's retry/forget routes take —
     * config/queue.php's `failed.driver` is `database-uuids`, whose provider
     * addresses jobs by that column, not this table's numeric `id`.
     *
     * @return array{count: int, latest: list<array{id: int, uuid: string, queue: string, message: ?string, failed_at: ?string}>}
     */
    private function failedJobs(): array
    {
        try {
            $count = DB::table('failed_jobs')->count();

            $latest = DB::table('failed_jobs')
                ->orderByDesc('failed_at')
                ->limit(5)
                ->get(['id', 'uuid', 'queue', 'exception', 'failed_at'])
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'uuid' => $row->uuid,
                    'queue' => $row->queue,
                    'message' => $this->firstLine($row->exception),
                    'failed_at' => $row->failed_at ? Carbon::parse($row->failed_at)->toIso8601String() : null,
                ])
                ->all();

            return ['count' => $count, 'latest' => $latest];
        } catch (Throwable $e) {
            $this->logDegraded('failed_jobs', $e);

            return ['count' => 0, 'latest' => []];
        }
    }

    /**
     * @return array{count: ?int, driver: string}
     */
    private function pendingJobs(): array
    {
        $driver = config('queue.default');

        // Only the database driver's backlog is queryable this way; other
        // drivers (sync, redis, sqs, ...) don't expose a countable table here.
        if ($driver !== 'database') {
            return ['count' => null, 'driver' => $driver];
        }

        try {
            return ['count' => DB::table('jobs')->count(), 'driver' => $driver];
        } catch (Throwable $e) {
            $this->logDegraded('pending_jobs', $e);

            return ['count' => null, 'driver' => $driver];
        }
    }

    /**
     * @return array{hiscores_last_synced_at: ?string, collection_log_last_synced_at: ?string}
     */
    private function syncFreshness(): array
    {
        $hiscores = null;

        try {
            $hiscores = AccountHiscore::query()->max('fetched_at');
            $hiscores = $hiscores ? Carbon::parse($hiscores)->toIso8601String() : null;
        } catch (Throwable $e) {
            $this->logDegraded('hiscores_last_synced_at', $e);
        }

        $collectionLog = null;

        try {
            // orderByDesc()->first() trips a laravel-mongodb sort-direction bug
            // on this connection; max() takes the same aggregation path
            // AccountHiscore's query above uses and sidesteps it.
            $collectionLog = CollectionLog::query()->max('fetched_at');
            $collectionLog = $collectionLog ? Carbon::parse($collectionLog)->toIso8601String() : null;
        } catch (Throwable $e) {
            $this->logDegraded('collection_log_last_synced_at', $e);
        }

        return [
            'hiscores_last_synced_at' => $hiscores,
            'collection_log_last_synced_at' => $collectionLog,
        ];
    }

    /**
     * @return array{screenshots_bytes: ?int, resource_packs_bytes: ?int}
     */
    private function storageUsage(): array
    {
        return [
            'screenshots_bytes' => Cache::remember(
                'system-health:storage:screenshots',
                self::STORAGE_CACHE_SECONDS,
                fn () => $this->directorySize((new ScreenshotStorage)->disk()->path(ScreenshotStorage::DIRECTORY)),
            ),
            'resource_packs_bytes' => Cache::remember(
                'system-health:storage:resource-packs',
                self::STORAGE_CACHE_SECONDS,
                fn () => $this->directorySize(public_path('resource-packs')),
            ),
        ];
    }

    /** Sum of file sizes under a directory, or null when it doesn't exist / can't be read. */
    private function directorySize(string $path): ?int
    {
        if (! File::isDirectory($path)) {
            return null;
        }

        try {
            $bytes = 0;

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $bytes += $file->getSize();
                }
            }

            return $bytes;
        } catch (Throwable $e) {
            $this->logDegraded('directory_size:'.$path, $e);

            return null;
        }
    }

    private function firstLine(string $exception): ?string
    {
        $line = strtok($exception, "\n");

        return $line !== false ? $line : null;
    }

    private function logDegraded(string $reading, Throwable $e): void
    {
        Log::warning("SystemHealth: {$reading} unavailable", ['exception' => $e->getMessage()]);
    }
}
