<?php

namespace App\Services\Screenshots;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Move screenshots stored on the public disk under their old guessable name
 * (`feed-screenshots/{eventId}.png`) to screenshot storage under a random name.
 *
 * Only rows in that legacy scheme are looked at: a row that already has a
 * random name is never checked or changed, so a transient permission problem
 * on a routine run cannot touch it.
 *
 * Nothing is lost on a failure: a row is only rewritten once its private copy
 * is written and verified, and a public file is only deleted once no row points
 * at it any more. A failed read or write throws, leaving that row and its file
 * as they were. A legacy row whose public file is missing is reported and kept,
 * unless `$pruneMissing` asks for its path to be cleared. Public files no row
 * points at are reported, never deleted.
 *
 * Idempotent, so it runs from the migration (pruning, once) and again after
 * every deploy's activation (not pruning), catching files the old release
 * wrote meanwhile.
 */
class MoveScreenshotsToPrivateDisk
{
    public function __construct(private ScreenshotStorage $storage) {}

    /**
     * @return array{moved: int, missing: list<int>, pruned: int, unreferenced: list<string>}
     */
    public function run(bool $pruneMissing = false): array
    {
        $public = Storage::disk('public');
        $moved = 0;
        $pruned = 0;
        $missing = [];

        DB::table('feed_events')
            ->where('screenshot_path', 'like', ScreenshotStorage::DIRECTORY.'/%')
            ->select(['id', 'screenshot_path'])
            ->chunkById(200, function ($rows) use ($public, $pruneMissing, &$moved, &$pruned, &$missing): void {
                foreach ($rows as $row) {
                    $path = (string) $row->screenshot_path;

                    if (ScreenshotStorage::isPrivateName($path) || ! ScreenshotStorage::isScreenshotPath($path)) {
                        continue;
                    }

                    if (! $public->exists($path)) {
                        if ($pruneMissing) {
                            DB::table('feed_events')->where('id', $row->id)
                                ->update(['screenshot_path' => null, 'screenshot_hidden' => false]);
                            $pruned++;
                        } else {
                            $missing[] = (int) $row->id;
                        }

                        continue;
                    }

                    DB::table('feed_events')->where('id', $row->id)
                        ->update(['screenshot_path' => $this->copy($path, (int) $row->id)]);
                    $moved++;

                    // Demo rows share one file: it goes with the last of them.
                    if (! DB::table('feed_events')->where('screenshot_path', $path)->exists()) {
                        $public->delete($path);
                    }
                }
            });

        return [
            'moved' => $moved,
            'missing' => $missing,
            'pruned' => $pruned,
            'unreferenced' => array_values($public->allFiles(ScreenshotStorage::DIRECTORY)),
        ];
    }

    /**
     * Copy one public screenshot into screenshot storage and return the new
     * path, throwing unless the copy is complete.
     */
    private function copy(string $path, int $eventId): string
    {
        $contents = Storage::disk('public')->get($path);

        if ($contents === null) {
            throw new RuntimeException("Could not read the screenshot of feed event {$eventId} ({$path}).");
        }

        $newPath = $this->storage->put($contents, pathinfo($path, PATHINFO_EXTENSION));

        if ($newPath === null || $this->storage->disk()->size($newPath) !== strlen($contents)) {
            $this->storage->delete($newPath);

            throw new RuntimeException("Could not copy the screenshot of feed event {$eventId} ({$path}) to private storage.");
        }

        return $newPath;
    }
}
