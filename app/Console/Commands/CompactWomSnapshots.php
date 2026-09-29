<?php

namespace App\Console\Commands;

use App\Models\WomDailySnapshot;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('wom:compact-snapshots')]
#[Description('Thin Wise Old Man daily snapshots older than 400 days down to one per account and ISO week.')]
class CompactWomSnapshots extends Command
{
    /** Newer documents stay daily, so year gains and one-year charts keep full detail. */
    public const DAILY_DAYS = 400;

    public function handle(): int
    {
        $cutoff = now()->utc()->subDays(self::DAILY_DAYS)->toDateString();

        // Read up to the end of the cutoff's ISO week: an old document is kept
        // only when it is its week's last, and that last one may be newer.
        $bound = Carbon::parse($cutoff)->endOfWeek(Carbon::SUNDAY)->toDateString();

        /** @var array<string, array{date: string, id: mixed}> $lastOfWeek "account:week" => newest doc */
        $lastOfWeek = [];
        $old = [];

        $docs = WomDailySnapshot::query()
            ->where('date', '<=', $bound)
            ->select(['_id', 'account_id', 'date'])
            ->cursor();

        foreach ($docs as $doc) {
            $week = $doc->account_id.':'.Carbon::parse($doc->date)->format('o-W');

            if (! isset($lastOfWeek[$week]) || $doc->date > $lastOfWeek[$week]['date']) {
                $lastOfWeek[$week] = ['date' => $doc->date, 'id' => $doc->getKey()];
            }

            if ($doc->date < $cutoff) {
                $old[] = ['week' => $week, 'id' => $doc->getKey()];
            }
        }

        $keep = array_map(fn (array $doc): string => (string) $doc['id'], $lastOfWeek);
        $delete = collect($old)
            ->filter(fn (array $doc): bool => $keep[$doc['week']] !== (string) $doc['id'])
            ->pluck('id');

        foreach ($delete->chunk(1000) as $ids) {
            WomDailySnapshot::query()->whereIn('_id', $ids->values()->all())->delete();
        }

        $this->info(sprintf('Removed %d snapshots.', $delete->count()));

        return self::SUCCESS;
    }
}
