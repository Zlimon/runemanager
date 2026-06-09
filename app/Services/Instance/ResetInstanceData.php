<?php

namespace App\Services\Instance;

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\Equipment;
use App\Models\FeedEvent;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Models\UsernameHistory;
use Spatie\Permission\Models\Role;

/**
 * SPEC §5/§12 — wipe all account data when the instance mode changes. The site's
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

        // Account-scoped relational tables (children first, then the accounts).
        AccountHiscore::query()->delete();
        Equipment::query()->delete();
        FeedEvent::query()->delete();
        UsernameHistory::query()->delete();
        Account::query()->delete();

        // Strip clan/group-derived roles back to member, leaving the owner alone.
        Role::findOrCreate('member', 'web');
        User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'owner'))
            ->get()
            ->each(fn (User $user) => $user->syncRoles(['member']));
    }
}
