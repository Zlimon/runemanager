<?php

namespace App\Services\Accounts;

use App\Http\Resources\AccountResource;
use App\Models\Account;

/**
 * The props every account tab shares: the header card (identity, live status
 * orbs, minimap) and the per-data-type freshness timestamps.
 */
class AccountProfile
{
    /**
     * @return array{account: array<string, mixed>, position: ?array{x: int, y: int, plane: int}, vitals: ?array<string, mixed>, location: ?string}
     */
    public function header(Account $account): array
    {
        $account->loadMissing('equipment');

        return [
            'account' => (new AccountResource($account))->resolve(),

            // Last known in-game position for the minimap; live updates arrive
            // over the shared map broadcast channel. Null when never shared.
            'position' => $account->world_x !== null
                ? ['x' => $account->world_x, 'y' => $account->world_y, 'plane' => $account->world_plane]
                : null,

            // Live status-orb values; updated over the account channel
            // (VitalsUpdated). Null until the plugin first pushes them.
            'vitals' => $account->vitalsPayload(),

            // Named area the character is in (for the minimap caption); updated
            // live over the account channel (StatusUpdated).
            'location' => $account->location,
        ];
    }

    /**
     * One ISO timestamp per requested data type, plus the staleness threshold
     * the UI uses to flag old data.
     *
     * @param  list<string>  $keys
     * @return array<string, string|int|null>
     */
    public function freshness(Account $account, array $keys): array
    {
        $resolvers = [
            'hiscores' => fn () => $account->hiscore?->fetched_at,
            'inventory' => fn () => $account->inventory?->updated_at,
            'bank' => fn () => $account->bank?->updated_at,
            'looting_bag' => fn () => $account->lootingBag?->updated_at,
            'quests' => fn () => $account->quests?->updated_at,
            'diaries' => fn () => $account->diary?->updated_at,
            'combat_achievements' => fn () => $account->combatAchievement?->updated_at,
            'equipment' => fn () => $account->equipment?->updated_at,
            'avatar' => fn () => $account->avatar_uploaded_at,
            'loot' => fn () => $account->latestLootKilledAt(),
        ];

        $freshness = [];

        foreach ($keys as $key) {
            $freshness[$key] = $resolvers[$key]()?->toIso8601String();
        }

        $freshness['stale_after_minutes'] = (int) config('runemanager.freshness.stale_after_minutes');

        return $freshness;
    }
}
