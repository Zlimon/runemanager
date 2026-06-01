<?php

use App\Models\CollectionLog;
use Illuminate\Database\Migrations\Migration;
use MongoDB\Client;

return new class extends Migration
{
    public function up(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $collection = $client->{config('database.connections.mongodb.database')}->{(new CollectionLog)->getTable()};

        $collection->createIndex(
            ['account_id' => 1],
            ['unique' => true]
        );
    }

    public function down(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $collection = $client->{config('database.connections.mongodb.database')}->{(new CollectionLog)->getTable()};

        $collection->dropIndex('account_id_1');
    }
};
