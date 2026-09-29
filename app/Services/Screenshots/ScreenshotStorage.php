<?php

namespace App\Services\Screenshots;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Where feed screenshots live: the private `local` disk, under a random
 * file name, so the only way to a screenshot is `screenshots.file`, which
 * checks who may see it. Every caller that stores or removes a screenshot
 * file goes through here.
 */
class ScreenshotStorage
{
    public const DISK = 'local';

    public const DIRECTORY = 'feed-screenshots';

    public function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    /** Store an uploaded screenshot and return its path on the disk, or null when the write failed. */
    public function store(UploadedFile $image): ?string
    {
        $path = $image->storeAs(self::DIRECTORY, self::fileName($image->extension()), self::DISK);

        return is_string($path) && $path !== '' ? $path : null;
    }

    /**
     * Store raw image bytes (a copy or a generated image) and return the path,
     * or null when the write failed.
     */
    public function put(string $contents, string $extension): ?string
    {
        $path = self::DIRECTORY.'/'.self::fileName($extension);

        return $this->disk()->put($path, $contents) ? $path : null;
    }

    /**
     * Remove screenshot files. Paths outside the screenshot directory are
     * ignored, so a stray value can never delete anything else.
     *
     * @param  iterable<int, ?string>|string|null  $paths
     */
    public function delete(iterable|string|null $paths): void
    {
        $paths = collect(is_iterable($paths) ? $paths : [$paths])
            ->filter(fn ($path): bool => is_string($path) && self::isScreenshotPath($path))
            ->values()
            ->all();

        if ($paths !== []) {
            $this->disk()->delete($paths);
        }
    }

    /** Remove every screenshot file (an instance reset). */
    public function deleteAll(): void
    {
        $this->disk()->deleteDirectory(self::DIRECTORY);
    }

    /** Whether a path has the random name this class gives files (as opposed to a legacy `{eventId}.png`). */
    public static function isPrivateName(string $path): bool
    {
        return preg_match('#^'.self::DIRECTORY.'/[A-Za-z0-9]{40}\.[a-z0-9]+$#', $path) === 1;
    }

    public static function isScreenshotPath(string $path): bool
    {
        return str_starts_with($path, self::DIRECTORY.'/') && ! str_contains($path, '..');
    }

    /** A name nobody can guess, so a path never leaks which event it belongs to. */
    private static function fileName(string $extension): string
    {
        $extension = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $extension) ?: 'png');

        return Str::random(40).'.'.$extension;
    }
}
