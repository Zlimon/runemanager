<?php

namespace App\Services\WiseOldMan;

use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\WomPlayer;

/**
 * The account owner opts in to Wise Old Man tracking: from now on `wom:sync`
 * may ask WOM to update the player. The queued first update creates the WOM
 * profile when there is none, which makes this the only path that may create
 * one. Makes no HTTP call itself, so it is safe inside a web request.
 */
class StartWomTracking
{
    public function start(Account $account): WomPlayer
    {
        // firstOrCreate falls back to a lookup if a concurrent request wins
        // the insert on the unique account_id.
        $player = WomPlayer::query()->firstOrCreate(['account_id' => $account->id]);

        if (! $player->tracking_opt_in) {
            $player->update(['tracking_opt_in' => true]);
        }

        SyncWomPlayerJob::dispatch($account->id, requestUpdate: true);

        return $player;
    }
}
