<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\WomPlayer;

/**
 * Apply an account owner's Wise Old Man choices from the settings page:
 * whether the account shows on /progress, and whether it may be tracked
 * (asked to update, which creates a public WOM profile when there is none).
 * A choice left null is unchanged.
 */
class UpdateWomPreferences
{
    public function __construct(
        private StartWomTracking $start,
        private StopWomTracking $stop,
    ) {}

    public function update(Account $account, ?bool $showOnProgress, ?bool $trackingOptIn): void
    {
        if ($showOnProgress !== null) {
            WomPlayer::query()->firstOrCreate(['account_id' => $account->id])
                ->update(['show_on_progress' => $showOnProgress]);
            $account->unsetRelation('womPlayer');
        }

        if ($trackingOptIn === true) {
            $this->start->start($account);
        } elseif ($trackingOptIn === false) {
            $this->stop->stop($account);
        }

        // Who appears on /progress and the boards may have changed.
        if ($showOnProgress !== null || $trackingOptIn !== null) {
            WomCache::bump();
        }
    }
}
