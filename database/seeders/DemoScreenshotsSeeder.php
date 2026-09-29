<?php

namespace Database\Seeders;

use App\Models\FeedEvent;
use App\Models\User;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Screenshots\ScreenshotStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

/**
 * Gives demo feed events small generated placeholder screenshots on the
 * private disk, some of them hidden, so the feed, the Log and Settings →
 * Screenshots have something to show. The demo login's accounts get a
 * gallery's worth; a few other members' events get some too.
 *
 * Idempotent: each group is only topped up to its target, counting the
 * screenshots it already has. Never runs in production.
 */
class DemoScreenshotsSeeder extends Seeder
{
    /** The demo login, whose gallery should fill more than one row. */
    private const DEMO_OWNER_EMAIL = 'zlimon@runemanager.com';

    /** Screenshots the demo login's accounts end up with. */
    private const OWNER_TARGET = 30;

    /** Screenshots other members' accounts end up with. */
    private const OTHERS_TARGET = 12;

    /** Every nth new screenshot arrives hidden. */
    private const HIDE_EVERY = 4;

    /** Background colours of the placeholders (RGB). */
    private const COLOURS = [[94, 76, 52], [52, 78, 94], [72, 94, 52], [94, 52, 72], [60, 60, 60]];

    public function run(ScreenshotStorage $storage): void
    {
        if (app()->environment('production')) {
            return;
        }

        $ownerId = User::where('email', self::DEMO_OWNER_EMAIL)->value('id');

        $owner = $ownerId !== null
            ? $this->fill($storage, fn (Builder $query) => $query->where('user_id', $ownerId), self::OWNER_TARGET)
            : 0;
        $others = $this->fill(
            $storage,
            fn (Builder $query) => $query->whereNotNull('user_id')->when($ownerId !== null, fn (Builder $query) => $query->where('user_id', '!=', $ownerId)),
            self::OTHERS_TARGET,
        );

        $this->command?->info(sprintf('DemoScreenshotsSeeder: added %d screenshots to the demo login\'s accounts and %d to other members\'.', $owner, $others));
    }

    /**
     * Top the matching accounts' events up to $target screenshots.
     *
     * @param  callable(Builder): mixed  $accounts
     */
    private function fill(ScreenshotStorage $storage, callable $accounts, int $target): int
    {
        $events = fn (): Builder => FeedEvent::query()
            ->whereHas('account', $accounts)
            ->whereIn('type', RecordFeedEvent::SCREENSHOT_TYPES);

        $missing = $target - $events()->withScreenshot()->count();

        if ($missing <= 0) {
            return 0;
        }

        $added = 0;

        foreach ($events()->whereNull('screenshot_path')->orderByDesc('occurred_at')->limit($missing)->get() as $event) {
            $path = $storage->put($this->placeholder($event), 'png');

            if ($path === null) {
                continue;
            }

            $event->update([
                'screenshot_path' => $path,
                'screenshot_hidden' => $event->id % self::HIDE_EVERY === 0,
            ]);
            $added++;
        }

        return $added;
    }

    /** A small PNG naming the event, standing in for a game frame. */
    private function placeholder(FeedEvent $event): string
    {
        $image = imagecreatetruecolor(320, 200);
        [$red, $green, $blue] = self::COLOURS[$event->id % count(self::COLOURS)];

        imagefill($image, 0, 0, imagecolorallocate($image, $red, $green, $blue));
        imagerectangle($image, 4, 4, 315, 195, imagecolorallocate($image, 200, 170, 90));

        $text = imagecolorallocate($image, 255, 240, 200);
        imagestring($image, 5, 16, 80, str_replace('_', ' ', (string) $event->type), $text);
        imagestring($image, 3, 16, 104, 'Demo screenshot #'.$event->id, $text);

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }
}
