<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Support\Carbon;

/**
 * Refresh one account's Wise Old Man row, today's daily point and, when
 * WOM saw a change, its achievements and records.
 *
 * WOM is only ever asked to update (POST) a player whose owner opted in
 * (`tracking_opt_in`), and then at most every {@see self::UPDATE_MIN_HOURS}
 * hours and only when our own data shows WOM is behind. Everyone else is
 * read with a GET, which never creates a WOM profile. A name WOM does not
 * know is looked up again after {@see self::NOT_FOUND_RETRY_DAYS} days.
 */
class SyncWomPlayer
{
    public const UPDATE_MIN_HOURS = 6;

    public const NOT_FOUND_RETRY_DAYS = 7;

    public function __construct(
        private WiseOldManClient $client,
        private BackfillWomSnapshots $backfill,
        private SyncWomMilestones $milestones,
    ) {}

    /**
     * Returns the account's row; a 404 is recorded on it as `not_found_at`.
     * `$requestUpdate` POSTs even when WOM is not behind (the owner just
     * opted in); the opt-in and the throttle still apply.
     *
     * @throws WiseOldManRateLimited
     */
    public function sync(Account $account, bool $requestUpdate = false): ?WomPlayer
    {
        $player = $account->womPlayer ?? new WomPlayer(['account_id' => $account->id]);

        if ($this->waitingOutNotFound($player)) {
            return $player;
        }

        $details = null;
        $fetched = false;

        if ($this->mayRequestUpdate($player) && ($requestUpdate || $this->isStale($account, $player))) {
            $player->update_requested_at = now();
            $details = $this->client->update($account->username);
            $fetched = $details !== null;
        }

        if (! $fetched) {
            $details = $this->client->player($account->username);
        }

        if ($details === null) {
            $player->not_found_at = now();
            $player->synced_at = now();
            $player->save();

            return $player;
        }

        $this->apply($player, $details);

        if ($player->backfilled_at === null) {
            $this->backfill->run($player, $account->username);
        }

        if ($this->milestonesAreBehind($player)) {
            $this->milestones->sync($player, $account->username);
        }

        return $player;
    }

    /**
     * Store a PlayerDetails payload on the row and upsert the day it describes,
     * with every metric of its `latestSnapshot`.
     * Public for callers that fetched the details themselves (`LiveWomSeeder`).
     *
     * @param  array<string, mixed>  $details
     */
    public function apply(WomPlayer $player, array $details): void
    {
        // WOM reports -1 for an unranked value: keep what the row already
        // holds rather than storing a false zero.
        $exp = (int) ($details['exp'] ?? -1);
        $ehp = (float) ($details['ehp'] ?? -1);
        $ehb = (float) ($details['ehb'] ?? -1);

        $player->fill([
            'wom_player_id' => (int) $details['id'],
            'type' => $details['type'] ?? null,
            'build' => $details['build'] ?? null,
            'status' => $details['status'] ?? null,
            'exp' => $exp > 0 ? $exp : ($player->exp ?? 0),
            'ehp' => $ehp >= 0 ? $ehp : ($player->ehp ?? 0),
            'ehb' => $ehb >= 0 ? $ehb : ($player->ehb ?? 0),
            'ttm' => max(0.0, (float) ($details['ttm'] ?? 0)),
            'tt200m' => max(0.0, (float) ($details['tt200m'] ?? 0)),
            'combat_level' => isset($details['combatLevel']) ? (int) $details['combatLevel'] : null,
            'registered_at' => $details['registeredAt'] ?? null,
            'wom_updated_at' => $details['updatedAt'] ?? null,
            'last_changed_at' => $details['lastChangedAt'] ?? null,
            'not_found_at' => null,
            'synced_at' => now(),
        ])->save();

        // An unranked total has no point to plot.
        if ($exp <= 0) {
            return;
        }

        $day = Carbon::parse($details['updatedAt'] ?? now())->utc()->toDateString();
        $row = ['account_id' => $player->account_id, 'date' => $day, 'exp' => $exp, 'ehp' => (float) $player->ehp, 'ehb' => (float) $player->ehb];

        // The latest snapshot carries every metric; without it the day keeps
        // whatever maps it already has.
        $data = $details['latestSnapshot']['data'] ?? null;
        $maps = is_array($data) ? WomMetrics::snapshotMaps($data) : [];

        WomDailySnapshot::query()->upsert([$row + $maps], ['account_id', 'date'], ['exp', 'ehp', 'ehb', ...array_keys($maps)]);
    }

    /**
     * Whether WOM saw the player change since achievements and records were
     * last read; an unchanged account costs no extra calls.
     */
    private function milestonesAreBehind(WomPlayer $player): bool
    {
        return $player->isLinked()
            && ($player->milestones_synced_at === null
                || ($player->last_changed_at !== null && $player->last_changed_at->gt($player->milestones_synced_at)));
    }

    /** An unlinked row whose last lookup 404'd recently, and no opt-in to POST. */
    private function waitingOutNotFound(WomPlayer $player): bool
    {
        return ! $player->isLinked()
            && ! $player->tracking_opt_in
            && $player->not_found_at !== null
            && $player->not_found_at->gt(now()->subDays(self::NOT_FOUND_RETRY_DAYS));
    }

    private function mayRequestUpdate(WomPlayer $player): bool
    {
        return $player->tracking_opt_in
            && ($player->update_requested_at === null
                || $player->update_requested_at->lte(now()->subHours(self::UPDATE_MIN_HOURS)));
    }

    /**
     * Whether WOM is behind what we know: never linked, our hiscores sweep
     * saw more XP, or the plugin saw the player online after WOM's last update.
     */
    private function isStale(Account $account, WomPlayer $player): bool
    {
        if (! $player->isLinked() || $player->wom_updated_at === null) {
            return true;
        }

        return (int) $account->xp > $player->exp
            || ($account->last_seen_at !== null && $account->last_seen_at->gt($player->wom_updated_at));
    }
}
