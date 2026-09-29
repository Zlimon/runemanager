<?php

use App\Models\WomDailySnapshot;
use Illuminate\Database\Migrations\Migration;
use MongoDB\Client;

/**
 * One Wise Old Man daily point per account and UTC day; the sync and the
 * backfill upsert on this pair.
 */
return new class extends Migration
{
    public function up(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $collection = $client->{config('database.connections.mongodb.database')}->{(new WomDailySnapshot)->getTable()};

        $collection->createIndex(
            ['account_id' => 1, 'date' => 1],
            ['unique' => true]
        );
    }

    public function down(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $collection = $client->{config('database.connections.mongodb.database')}->{(new WomDailySnapshot)->getTable()};

        $collection->dropIndex('account_id_1_date_1');
    }
};
