<?php

namespace App\Services\Instance;

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\Equipment;
use App\Models\FeedEvent;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Models\WomAchievement;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use App\Services\WiseOldMan\WomCache;
use App\Support\Roles;
use Illuminate\Support\Facades\Cache;

/**
 * Wipe all account data when the instance mode changes. The site's
 * whole context (clan vs group vs casual) differs per mode, so accounts start
 * fresh. Users are kept (they re-claim a roster account) but every non-owner is
 * reset to a plain `member`.
 */
class ResetInstanceData
{
    public function run(): void
    {
        // Account-scoped MongoDB collections.
        Inventory::query()->delete();
        Bank::query()->delete();
        LootingBag::query()->delete();
        Quest::query()->delete();
        CollectionLog::query()->delete();
        Loot::query()->delete();
        WomDailySnapshot::query()->delete();

        // Instance-scoped, not account-scoped: one shared document for the
        // Group Ironman group, which the new mode's group must not inherit.
        GroupBank::query()->delete();

        // Account-scoped relational tables (children first, then the accounts).
        AccountHiscore::query()->delete();
        Equipment::query()->delete();
        FeedEvent::query()->delete();
        UsernameHistory::query()->delete();
        WomAchievement::query()->delete();
        WomRecord::query()->delete();
        WomPlayer::query()->delete();
        Account::query()->delete();

        // The Hiscores nav lists are derived from account data, so they would
        // otherwise keep naming bosses this instance no longer has any hiscores for.
        Cache::forget('nav.bosses');
        Cache::forget('nav.skills');
        WomCache::bump();

        // Strip any elevated roles back to a plain User, leaving the Owner alone.
        Roles::sync();
        User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', Roles::OWNER))
            ->get()
            ->each(fn (User $user) => $user->syncRoles([Roles::USER]));
    }
}
