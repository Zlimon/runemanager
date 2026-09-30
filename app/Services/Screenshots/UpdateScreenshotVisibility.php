<?php

namespace App\Services\Screenshots;

use App\Models\FeedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Hide screenshots from everyone but their owner and the instance admins, or
 * show them again.
 */
class UpdateScreenshotVisibility
{
    /**
     * Only screenshots of the user's own accounts change; other ids are
     * skipped (Settings → Screenshots).
     *
     * @param  array<int, int>  $ids
     * @return int How many screenshots were updated.
     */
    public function update(User $user, array $ids, bool $hidden): int
    {
        return $this->apply(FeedEvent::query()->whereIn('id', $ids)->ownedBy($user), $hidden);
    }

    /**
     * Any account's screenshots, no ownership check — the admin moderation
     * queue, gated by `can:manage instance` at the route.
     *
     * @param  array<int, int>  $ids
     * @return int How many screenshots were updated.
     */
    public function updateAny(array $ids, bool $hidden): int
    {
        return $this->apply(FeedEvent::query()->whereIn('id', $ids), $hidden);
    }

    /** @param  Builder<FeedEvent>  $query */
    private function apply(Builder $query, bool $hidden): int
    {
        return $query->withScreenshot()->update(['screenshot_hidden' => $hidden]);
    }
}
