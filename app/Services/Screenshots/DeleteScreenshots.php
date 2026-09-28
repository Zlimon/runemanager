<?php

namespace App\Services\Screenshots;

use App\Models\FeedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Remove screenshots while keeping their feed events: the owner deleting
 * some from Settings → Screenshots, or an account changing hands (the
 * screenshots were the previous owner's to share, not the next one's).
 */
class DeleteScreenshots
{
    public function __construct(private ScreenshotStorage $storage) {}

    /**
     * Delete the screenshots of those events that belong to the user's own
     * accounts; other ids are skipped. Returns how many were deleted.
     *
     * @param  array<int, int>  $ids
     */
    public function delete(User $user, array $ids): int
    {
        return $this->clear(FeedEvent::query()->whereIn('id', $ids)->ownedBy($user));
    }

    /**
     * Delete every screenshot of the given accounts. Safe inside a transaction:
     * the files go once it commits, so a rolled-back change keeps them.
     *
     * @param  array<int, int>  $accountIds
     */
    public function forAccounts(array $accountIds): int
    {
        return $this->clear(FeedEvent::query()->whereIn('account_id', $accountIds));
    }

    /** @param  Builder<FeedEvent>  $query */
    private function clear(Builder $query): int
    {
        $events = $query->withScreenshot()->get(['id', 'screenshot_path']);

        if ($events->isEmpty()) {
            return 0;
        }

        FeedEvent::query()->whereIn('id', $events->modelKeys())
            ->update(['screenshot_path' => null, 'screenshot_hidden' => false]);

        $paths = $events->pluck('screenshot_path')->all();
        DB::afterCommit(fn () => $this->storage->delete($paths));

        return $events->count();
    }
}
