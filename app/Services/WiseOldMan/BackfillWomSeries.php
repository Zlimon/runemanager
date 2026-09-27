<?php

namespace App\Services\WiseOldMan;

use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Support\Carbon;

/**
 * Load a newly linked player's last year of history into `WomDailySnapshot`,
 * once. WOM's timelines hold a point per snapshot (often several a day);
 * each is downsampled to the last value of its UTC day.
 */
class BackfillWomSeries
{
    /** WOM timeline metric => snapshot field. */
    private const METRICS = ['overall' => 'exp', 'ehp' => 'ehp', 'ehb' => 'ehb'];

    public function __construct(private WiseOldManClient $client) {}

    /**
     * Returns the number of days written.
     *
     * @throws WiseOldManRateLimited
     */
    public function run(WomPlayer $player, ?string $username = null): int
    {
        $username ??= $player->account->username;

        /** @var array<string, array<string, array{at: Carbon, value: int|float}>> $byDay date => field => last point */
        $byDay = [];

        foreach (self::METRICS as $metric => $field) {
            foreach ($this->client->timeline($username, $metric, 'year') as $point) {
                // Unranked (-1): let an earlier valid point of the day stand.
                if ($field === 'exp' ? $point['value'] <= 0 : $point['value'] < 0) {
                    continue;
                }

                $at = Carbon::parse($point['date'])->utc();
                $day = $at->toDateString();

                if (! isset($byDay[$day][$field]) || $at->gte($byDay[$day][$field]['at'])) {
                    $byDay[$day][$field] = ['at' => $at, 'value' => $point['value']];
                }
            }
        }

        ksort($byDay);

        // A metric with no valid point on a day carries the previous day's
        // value; a day before any valid value of a metric is left out, so the
        // series never shows a false zero.
        $previous = ['exp' => null, 'ehp' => null, 'ehb' => null];
        $rows = [];

        foreach ($byDay as $day => $fields) {
            $row = [
                'exp' => isset($fields['exp']) ? (int) $fields['exp']['value'] : $previous['exp'],
                'ehp' => isset($fields['ehp']) ? (float) $fields['ehp']['value'] : $previous['ehp'],
                'ehb' => isset($fields['ehb']) ? (float) $fields['ehb']['value'] : $previous['ehb'],
            ];
            $previous = $row;

            if (in_array(null, $row, true)) {
                continue;
            }

            $rows[] = ['account_id' => $player->account_id, 'date' => $day] + $row;
        }

        // One bulk write, keyed on the (account_id, date) unique index.
        WomDailySnapshot::query()->upsert($rows, ['account_id', 'date'], ['exp', 'ehp', 'ehb']);
        $written = count($rows);

        $player->forceFill(['backfilled_at' => now()])->save();

        return $written;
    }
}
