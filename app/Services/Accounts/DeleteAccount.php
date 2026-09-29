<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\FeedEvent;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Services\Screenshots\ScreenshotStorage;

/**
 * Remove one account and everything keyed to it.
 *
 * The relational children (hiscores, diaries, combat achievements, equipment,
 * feed events, username histories, calendar RSVPs) cascade at the database. The
 * MongoDB snapshots are keyed on `account_id` with no constraint behind them,
 * so they have to be cleared here or they outlive the account forever. The
 * group bank is instance-wide, not account-scoped, and stays. The feed
 * events' screenshots and the 3D avatar are files, so they are removed here
 * too.
 */
class DeleteAccount
{
    public function __construct(private ScreenshotStorage $screenshots, private StoreAvatar $avatars) {}

    public function run(Account $account): void
    {
        $screenshotPaths = FeedEvent::query()->where('account_id', $account->id)->withScreenshot()->pluck('screenshot_path')->all();

        // The account row goes first: if deleting it fails (a legacy table
        // still references it), the snapshots are still there. The other order
        // would wipe a live account's bank and loot and leave no way back.
        $account->delete();

        Inventory::where('account_id', $account->id)->delete();
        Bank::where('account_id', $account->id)->delete();
        LootingBag::where('account_id', $account->id)->delete();
        Quest::where('account_id', $account->id)->delete();
        CollectionLog::where('account_id', $account->id)->delete();
        Loot::where('account_id', $account->id)->delete();

        $this->screenshots->delete($screenshotPaths);
        $this->avatars->delete($account);
    }
}
