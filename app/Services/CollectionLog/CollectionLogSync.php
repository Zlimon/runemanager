<?php

namespace App\Services\CollectionLog;

use App\Models\Account;
use App\Models\CollectionLog;
use App\Services\TempleOsrs\TempleOsrsClient;

/**
 * SPEC §5.2 — pull an account's collection log from TempleOSRS and upsert it.
 * TempleOSRS returns overall progress counts plus the obtained items keyed by
 * category slug; we store that as the single source for the profile view and
 * the leaderboard.
 */
class CollectionLogSync
{
    public function __construct(private TempleOsrsClient $client) {}

    /**
     * @return CollectionLog|null null when the player hasn't synced on TempleOSRS
     */
    public function syncForAccount(Account $account): ?CollectionLog
    {
        $data = $this->client->collectionLog($account->username);
        if ($data === null) {
            return null;
        }

        $log = CollectionLog::where('account_id', $account->id)->first()
            ?? (new CollectionLog)->forceFill(['account_id' => $account->id]);

        $log->forceFill([
            'obtained' => (int) ($data['total_collections_finished'] ?? 0),
            'total' => (int) ($data['total_collections_available'] ?? 0),
            'categories_finished' => (int) ($data['total_categories_finished'] ?? 0),
            'categories_available' => (int) ($data['total_categories_available'] ?? 0),
            'items' => $data['items'] ?? [],
            'fetched_at' => now(),
        ])->save();

        return $log;
    }
}
