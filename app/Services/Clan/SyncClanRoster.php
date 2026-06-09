<?php

namespace App\Services\Clan;

use App\Enums\AccountTypesEnum;
use App\Helpers\SettingHelper;
use App\Models\Account;

/**
 * SPEC §5.2 — ingest the clan roster pushed by the owner's plugin. Each member
 * is pre-created as an unclaimed account (username only, no user, no hash) that
 * the owning player links on their first plugin login. Existing accounts keep
 * their link + hash and just have their rank/title refreshed.
 */
class SyncClanRoster
{
    public function __construct(private SyncClanRole $syncClanRole) {}

    /**
     * @param  array<int, array{username: string, rank?: int|null, title?: string|null}>  $members
     * @return int the number of roster members upserted
     */
    public function ingest(?string $clanName, array $members): int
    {
        if ($clanName !== null && trim($clanName) !== '') {
            SettingHelper::setSetting('clan_name', trim($clanName));
        }

        $count = 0;

        foreach ($members as $member) {
            $username = trim($member['username']);
            if ($username === '') {
                continue;
            }

            $account = Account::query()->where('username', $username)->first();

            if ($account === null) {
                $account = new Account;
                $account->username = $username;
                $account->user_id = null;
                $account->account_hash = null;
                $account->account_type = AccountTypesEnum::NORMAL;
                $account->rank = 0;
                $account->level = 0;
                $account->xp = 0;
            }

            $account->clan_rank = $member['rank'] ?? null;
            $account->clan_title = $member['title'] ?? null;
            $account->save();

            // Refresh the role of members who have already claimed their account.
            if ($account->user_id !== null) {
                $this->syncClanRole->forAccount($account);
            }

            $count++;
        }

        return $count;
    }
}
