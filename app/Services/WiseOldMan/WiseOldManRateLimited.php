<?php

namespace App\Services\WiseOldMan;

use RuntimeException;

/**
 * Wise Old Man answered 429, or our own request budget for the minute is
 * spent. Queued callers release the job for `$retryAfter` seconds; page
 * renders fall back to their empty state.
 */
class WiseOldManRateLimited extends RuntimeException
{
    public function __construct(public readonly int $retryAfter)
    {
        parent::__construct("Wise Old Man rate limit reached; retry after {$retryAfter}s.");
    }
}
