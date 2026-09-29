<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\WomPlayer;

/**
 * The account owner withdraws consent: from now on the account is only read
 * from Wise Old Man, never asked to update. The WOM profile itself stays.
 */
class StopWomTracking
{
    public function stop(Account $account): ?WomPlayer
    {
        $player = $account->womPlayer;

        $player?->update(['tracking_opt_in' => false]);
        WomCache::bump();

        return $player;
    }
}
