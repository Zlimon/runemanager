<?php

use App\Models\PluginRequest;
use Illuminate\Database\Migrations\Migration;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Indexes for the plugin traffic monitor: every read is bounded by a time
 * window, the per-user page filters by user, and the flagged feed by flag.
 * Retention is the scheduled `plugin-traffic:prune` (keeps
 * PrunePluginRequests::RETENTION_DAYS) rather than a TTL index.
 */
return new class extends Migration
{
    public function up(): void
    {
        $collection = $this->collection();

        $collection->createIndex(['created_at' => -1]);
        $collection->createIndex(['user_id' => 1, 'created_at' => -1]);
        $collection->createIndex(['flags' => 1, 'created_at' => -1]);
    }

    public function down(): void
    {
        $collection = $this->collection();

        $collection->dropIndex('created_at_-1');
        $collection->dropIndex('user_id_1_created_at_-1');
        $collection->dropIndex('flags_1_created_at_-1');
    }

    private function collection(): Collection
    {
        $client = new Client(config('database.connections.mongodb.dsn'));

        return $client->{config('database.connections.mongodb.database')}->{(new PluginRequest)->getTable()};
    }
};
