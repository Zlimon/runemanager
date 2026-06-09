<?php

namespace App\Services\Clan;

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Support\Instance;
use Spatie\Permission\Models\Role;

/**
 * SPEC §5.2 — mirror in-game clan ranks onto website roles while the instance
 * runs in CLAN mode. A member at ClanRank ADMINISTRATOR or above becomes an
 * `admin`; everyone else in the configured clan is a `member`.
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
     * of their accounts in the configured clan. No-op outside CLAN mode, when no
     * clan is configured, or when the account isn't in that clan.
     */
    public function forAccount(Account $account): void
    {
        if (! Instance::isClan()) {
            return;
        }

        $clan = trim((string) SettingHelper::getSetting('clan_name', ''));
        if ($clan === '' || ! $this->matchesClan($account->clan_name, $clan)) {
            return;
        }

        $user = $account->user;
        if ($user === null || $user->hasRole('owner')) {
            return;
        }

        $maxRank = Account::query()
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(TRIM(clan_name)) = ?', [mb_strtolower($clan)])
            ->max('clan_rank');

        $role = $maxRank !== null && $maxRank >= self::CLAN_ADMIN_RANK ? 'admin' : 'member';
        Role::findOrCreate($role, 'web');

        $current = $user->getRoleNames();
        if ($current->count() !== 1 || $current->first() !== $role) {
            $user->syncRoles([$role]);
        }
    }

    private function matchesClan(?string $accountClan, string $configuredClan): bool
    {
        return $accountClan !== null
            && mb_strtolower(trim($accountClan)) === mb_strtolower($configuredClan);
    }
}
