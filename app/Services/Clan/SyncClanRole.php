<?php

namespace App\Services\Clan;

use App\Models\Account;
use App\Support\Instance;
use Spatie\Permission\Models\Role;

/**
 * SPEC §5.2 — mirror in-game clan ranks onto website roles while the instance
 * runs in CLAN mode. The whole site is a single clan, so the rank alone drives
 * the role: a member at ClanRank ADMINISTRATOR or above becomes an `admin`,
 * everyone else a `member`.
 *
 * The instance owner is never touched — the bootstrap owner (first registered
 * user) keeps control regardless of their in-game rank.
 */
class SyncClanRole
{
    /** RuneLite ClanRank.ADMINISTRATOR — the lowest rank with clan-admin powers. */
    public const CLAN_ADMIN_RANK = 100;

    /**
     * Reconcile the account owner's role from their highest clan rank across all
     * of their accounts. No-op outside CLAN mode or for unclaimed accounts.
     */
    public function forAccount(Account $account): void
    {
        if (! Instance::isClan() || $account->user_id === null) {
            return;
        }

        $user = $account->user;
        if ($user === null || $user->hasRole('owner')) {
            return;
        }

        $maxRank = Account::query()
            ->where('user_id', $user->id)
            ->max('clan_rank');

        $role = $maxRank !== null && $maxRank >= self::CLAN_ADMIN_RANK ? 'admin' : 'member';
        Role::findOrCreate($role, 'web');

        $current = $user->getRoleNames();
        if ($current->count() !== 1 || $current->first() !== $role) {
            $user->syncRoles([$role]);
        }
    }
}
