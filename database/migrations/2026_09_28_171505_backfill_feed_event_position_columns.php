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
     * Copy the position of events recorded before the columns existed out of
     * their payload, through FeedEvent::position() so the backfill and the
     * saving hook agree. Rows of a chunk that share a spot are updated in one
     * statement. Only rows without a position yet are read, so it is safe to
     * re-run.
     */
    public function up(): void
    {
        DB::table('feed_events')
            ->whereNull('world_x')
            ->select(['id', 'payload'])
            ->chunkById(500, function ($rows): void {
                $groups = [];

                foreach ($rows as $row) {
                    $payload = json_decode((string) $row->payload, true);
                    $position = FeedEvent::position(is_array($payload) ? $payload : []);

                    if ($position['world_x'] !== null) {
                        $key = json_encode($position);
                        $groups[$key]['position'] = $position;
                        $groups[$key]['ids'][] = (int) $row->id;
                    }
                }

                foreach ($groups as $group) {
                    DB::table('feed_events')->whereIn('id', $group['ids'])->update($group['position']);
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
