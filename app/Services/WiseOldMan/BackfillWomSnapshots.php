<?php

namespace App\Services\WiseOldMan;

use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Support\Carbon;

/**
 * Load a linked player's last year of history into `WomDailySnapshot`, once
 * per link (and again whenever `backfilled_at` is cleared). Pages through
 * WOM's snapshots, newest first; each carries every metric, and the last one
 * of each UTC day becomes that day's document. Existing days are updated in
 * place, so a headline-only document gains its metric maps.
 *
 * WOM snapshots some players many times a day; for those the
 * {@see self::MAX_PAGES} cap gives a shorter history rather than an
 * unbounded number of calls.
 */
class BackfillWomSnapshots
{
    /** WOM's largest page. */
    public const PAGE_SIZE = 200;

    public const MAX_PAGES = 10;

    public const DAYS = 365;

    public function __construct(private WiseOldManClient $client) {}

    /**
     * Returns the number of days written.
     *
     * @throws WiseOldManRateLimited
     */
    public function run(WomPlayer $player, ?string $username = null): int
    {
        $username ??= $player->account->username;
        $cutoff = now()->utc()->subDays(self::DAYS);

        /** @var array<string, array{at: Carbon, data: array<string, mixed>}> $byDay date => last snapshot */
        $byDay = [];

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $snapshots = $this->client->snapshots($username, 'year', self::PAGE_SIZE, $page * self::PAGE_SIZE);
            $reachedCutoff = false;

            foreach ($snapshots as $snapshot) {
                $at = Carbon::parse($snapshot['createdAt'])->utc();

                if ($at->lt($cutoff)) {
                    $reachedCutoff = true;

                    continue;
                }

                $day = $at->toDateString();

                if (! isset($byDay[$day]) || $at->gt($byDay[$day]['at'])) {
                    $byDay[$day] = ['at' => $at, 'data' => (array) ($snapshot['data'] ?? [])];
                }
            }

            if ($reachedCutoff || count($snapshots) < self::PAGE_SIZE) {
                break;
            }
        }

        ksort($byDay);

        // An unranked (-1) headline value carries the previous day's; a day
        // before any valid total is left out, so charts never show a false zero.
        $previous = ['exp' => null, 'ehp' => null, 'ehb' => null];
        $rows = [];

        foreach ($byDay as $day => $snapshot) {
            $data = $snapshot['data'];
            $exp = (int) ($data['skills']['overall']['experience'] ?? -1);
            $ehp = (float) ($data['computed']['ehp']['value'] ?? -1);
            $ehb = (float) ($data['computed']['ehb']['value'] ?? -1);

            $row = [
                'exp' => $exp > 0 ? $exp : $previous['exp'],
                'ehp' => $ehp >= 0 ? $ehp : $previous['ehp'],
                'ehb' => $ehb >= 0 ? $ehb : $previous['ehb'],
            ];
            $previous = $row;

            if (in_array(null, $row, true)) {
                continue;
            }

            $rows[] = ['account_id' => $player->account_id, 'date' => $day] + $row + WomMetrics::snapshotMaps($data);
        }

        // One bulk write, keyed on the (account_id, date) unique index.
        if ($rows !== []) {
            WomDailySnapshot::query()->upsert($rows, ['account_id', 'date'], ['exp', 'ehp', 'ehb', 'skills', 'bosses', 'activities']);
        }

        $player->forceFill(['backfilled_at' => now()])->save();

        return count($rows);
    }
}
