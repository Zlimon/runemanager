<?php

use App\Models\FeedEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Each statement commits on its own, so the plugin pushes the old release
     * still serves during a deploy never wait on one long transaction.
     */
    public $withinTransaction = false;

    /**
     * Classify the feed events recorded before the milestone columns existed.
     * Types that are always milestones take one statement; level-ups and loot
     * depend on their payload, so they go through FeedEvent::classify() in
     * chunks, with one update per outcome per chunk. Safe to re-run.
     */
    public function up(): void
    {
        DB::table('feed_events')
            ->whereIn('type', FeedEvent::MILESTONE_TYPES)
            ->update(['milestone' => true]);

        DB::table('feed_events')
            ->whereIn('type', [FeedEvent::TYPE_LEVEL_UP, FeedEvent::TYPE_LOOT_DROP])
            ->select(['id', 'type', 'payload'])
            ->chunkById(500, function ($rows): void {
                $milestones = [];
                $others = [];
                $values = [];

                foreach ($rows as $row) {
                    $payload = json_decode((string) $row->payload, true);
                    $classified = FeedEvent::classify($row->type, is_array($payload) ? $payload : []);

                    if ($classified['milestone']) {
                        $milestones[] = (int) $row->id;
                    } else {
                        $others[] = (int) $row->id;
                    }

                    if ($classified['value'] !== null) {
                        $values[(int) $row->id] = (int) $classified['value'];
                    }
                }

                if ($milestones !== []) {
                    DB::table('feed_events')->whereIn('id', $milestones)->update(['milestone' => true]);
                }

                if ($others !== []) {
                    DB::table('feed_events')->whereIn('id', $others)->update(['milestone' => false]);
                }

                if ($values !== []) {
                    // Integers only, so they can be inlined into the CASE.
                    $cases = implode(' ', array_map(
                        fn (int $id, int $value): string => "WHEN {$id} THEN {$value}",
                        array_keys($values),
                        $values,
                    ));

                    DB::table('feed_events')
                        ->whereIn('id', array_keys($values))
                        ->update(['value' => DB::raw("CASE id {$cases} END")]);
                }
            });
    }

    /**
     * The columns themselves are dropped by the migration that added them.
     */
    public function down(): void
    {
        //
    }
};
