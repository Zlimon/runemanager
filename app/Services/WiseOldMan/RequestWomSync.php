<?php

namespace App\Services\WiseOldMan;

use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\WomPlayer;
use Illuminate\Support\Carbon;

/**
 * The account owner asks for a Wise Old Man refresh from Settings → Privacy,
 * at most once every {@see self::COOLDOWN_HOURS} hours per account. The
 * queued job keeps {@see SyncWomPlayer}'s rules: it only POSTs an update for
 * an opted-in account (and not within its update throttle), everyone else is
 * read with a GET. Makes no HTTP call itself, so it is safe inside a web request.
 */
class RequestWomSync
{
    public const COOLDOWN_HOURS = 24;

    /** Whether the sync was queued; false while the account is cooling down. */
    public function request(Account $account): bool
    {
        $player = WomPlayer::query()->firstOrCreate(['account_id' => $account->id]);

        // One conditional write, so two quick clicks cannot both get through.
        $claimed = WomPlayer::query()
            ->whereKey($player->id)
            ->where(fn ($query) => $query
                ->whereNull('manual_sync_requested_at')
                ->orWhere('manual_sync_requested_at', '<=', now()->subHours(self::COOLDOWN_HOURS)))
            ->update(['manual_sync_requested_at' => now()]);

        if ($claimed === 0) {
            return false;
        }

        SyncWomPlayerJob::dispatch($account->id, requestUpdate: true);

        return true;
    }

    /** When the owner may next ask, or null when they may now. */
    public static function availableAt(?WomPlayer $player): ?Carbon
    {
        $next = $player?->manual_sync_requested_at?->copy()->addHours(self::COOLDOWN_HOURS);

        return $next?->isFuture() ? $next : null;
    }
}
