<?php

namespace App\Services\Screenshots;

use App\Models\FeedEvent;
use App\Models\User;

/**
 * Hide screenshots from everyone but their owner and the instance admins, or
 * show them again. Only screenshots of the user's own accounts change; other
 * ids are skipped.
 */
class UpdateScreenshotVisibility
{
    /**
     * @param  array<int, int>  $ids
     * @return int How many screenshots were updated.
     */
    public function update(User $user, array $ids, bool $hidden): int
    {
        return FeedEvent::query()
            ->whereIn('id', $ids)
            ->ownedBy($user)
            ->withScreenshot()
            ->update(['screenshot_hidden' => $hidden]);
    }
}
