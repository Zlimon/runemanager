<?php

namespace App\Http\Controllers;

use App\Models\FeedEvent;
use App\Services\Screenshots\ScreenshotStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScreenshotFileController extends Controller
{
    /** How long a browser may reuse a screenshot before asking again. */
    private const MAX_AGE_SECONDS = 300;

    /**
     * A feed event's screenshot, for whoever may see it
     * ({@see FeedEvent::screenshotVisibleTo()}); guests included, as the feed
     * is public. A hidden screenshot is a 404 like a missing one, so its
     * existence is not given away. Only the viewer's own browser caches it,
     * and briefly, so hiding one takes effect soon for everyone.
     */
    public function show(Request $request, FeedEvent $feedEvent, ScreenshotStorage $storage): BinaryFileResponse
    {
        $path = $feedEvent->screenshot_path;

        abort_unless(
            $feedEvent->screenshotVisibleTo($request->user())
                && ScreenshotStorage::isScreenshotPath((string) $path)
                && $storage->disk()->exists((string) $path),
            404,
        );

        $lastModified = $storage->disk()->lastModified((string) $path);

        $response = response()->file($storage->disk()->path((string) $path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->setMaxAge(self::MAX_AGE_SECONDS);
        $response->setEtag(sha1($path.'|'.$lastModified));
        $response->setLastModified(Carbon::createFromTimestamp($lastModified));
        $response->isNotModified($request);

        return $response;
    }
}
